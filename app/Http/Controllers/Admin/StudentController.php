<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Mail\StudentCredentialsMail;
use App\Models\EmailLog;
use App\Models\User;
use App\Support\AppUrl;
use App\Services\AttendanceService;
use App\Services\StudentNumberService;
use App\Services\StudentService;
use App\Support\StudentSectionRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PDOException;
use Throwable;

class StudentController extends Controller
{
    public function index(StudentService $studentService): View
    {
        return view('admin.students.index', [
            'students' => $studentService->getStudents(),
            'studentService' => $studentService,
        ]);
    }

    public function show(int $student, AttendanceService $attendanceService): View
    {
        $record = $this->studentQuery()
            ->with(['role', 'department', 'section'])
            ->findOrFail($student);

        return view('admin.students.show', [
            'student' => $record,
            'semesterSummary' => $attendanceService->getStudentSemesterSummary($record),
        ]);
    }

    public function create(StudentService $studentService): View
    {
        $sections = $studentService->getSections();

        return view('admin.students.create', [
            'roles' => $studentService->getRoles(),
            'departments' => $studentService->getDepartments(),
            'sections' => $sections,
            'sectionRules' => StudentSectionRules::map(),
            'sectionsForDropdown' => $this->sectionsForDropdown($sections),
        ]);
    }

    public function store(StoreStudentRequest $request, StudentNumberService $studentNumberService): RedirectResponse
    {
        $validated = $request->validated();
        unset($validated['year']);
        $validated['must_change_password'] = true;
        $validated['qr_token'] = $validated['qr_token'] ?? (string) Str::uuid();

        // Generate a readable temporary password when the form leaves it blank.
        $plainPassword = filled($validated['password'] ?? null)
            ? $validated['password']
            : $this->generateTemporaryPassword();
        $validated['password'] = $plainPassword;

        // Auto-generate student number and create record within transaction to prevent race conditions
        $maxRetries = 3;
        $attempt = 0;
        $student = null;

        while ($attempt < $maxRetries && $student === null) {
            try {
                $student = DB::transaction(function () use ($validated, $studentNumberService) {
                    $data = $validated; // Copy to allow modification

                    // Auto-generate student number if not provided, inside transaction
                    if (blank($data['student_number'] ?? null)) {
                        $data['student_number'] = $studentNumberService->generate();
                    }

                    return User::query()->create($data);
                });
            } catch (PDOException $e) {
                // Check if it's a duplicate key error for student_number
                if (str_contains($e->getMessage(), 'Duplicate entry') &&
                    str_contains($e->getMessage(), 'users_student_number_unique')) {
                    $attempt++;
                    if ($attempt >= $maxRetries) {
                        throw $e;
                    }
                } else {
                    throw $e;
                }
            }
        }

        $statusMessage = 'Account created successfully.';

        $mailSent = $this->sendCredentialMail($student, $plainPassword);
        $mailMessage = $mailSent
            ? 'Credentials email sent successfully.'
            : 'The account was saved, but the credentials email could not be sent with the current mail configuration.';

        return redirect()
            ->route('admin.students.index')
            ->with('success', trim($statusMessage.' '.$mailMessage));
    }

    public function edit(int $student, StudentService $studentService): View
    {
        $sections = $studentService->getSections();

        return view('admin.students.edit', [
            'student' => $studentService->findStudent($student),
            'roles' => $studentService->getRoles(),
            'departments' => $studentService->getDepartments(),
            'sections' => $sections,
            'sectionRules' => StudentSectionRules::map(),
            'sectionsForDropdown' => $this->sectionsForDropdown($sections),
        ]);
    }

    public function update(StoreStudentRequest $request, int $student): RedirectResponse
    {
        $record = $this->studentQuery()->findOrFail($student);
        $validated = $request->validated();
        unset($validated['year']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['must_change_password'] = true;
        }

        if (blank($validated['student_number'] ?? null)) {
            $validated['student_number'] = app(StudentNumberService::class)->generate();
        }

        $record->update($validated);

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Account updated successfully.');
    }

    public function resendCredentials(int $student): RedirectResponse
    {
        $record = $this->studentQuery()->findOrFail($student);

        if (blank($record->email)) {
            return redirect()
                ->back()
                ->with('error', 'This student does not have an email address to send credentials to.');
        }

        $plainPassword = $this->generateTemporaryPassword();
        $originalPassword = $record->getRawOriginal('password');

        $record->password = $plainPassword;
        $record->must_change_password = true;
        $record->save();

        $mailSent = $this->sendCredentialMail($record->fresh(), $plainPassword);

        if (! $mailSent) {
            DB::table('users')
                ->where('id', $record->id)
                ->update([
                    'password' => $originalPassword,
                    'updated_at' => now(),
                ]);

            return redirect()
                ->back()
                ->with('error', 'The temporary password was not changed because the credentials email could not be sent.');
        }

        return redirect()
            ->back()
            ->with('success', 'A new temporary password was generated and sent to the student email address.');
    }

    public function destroy(int $student): RedirectResponse
    {
        $record = $this->studentQuery()->findOrFail($student);

        if ((int) Auth::id() === $record->id) {
            return redirect()
                ->route('admin.students.index')
                ->with('status', 'You cannot delete the account you are currently using.');
        }

        $record->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Account record removed successfully.');
    }

    private function sendCredentialMail(User $student, string $plainPassword): bool
    {
        if (blank($student->email)) {
            return false;
        }

        $subject = 'Your SSG Management System Access';
        $message = 'Your account is ready. Use your assigned email and password to access the student portal.';

        try {
            Mail::to($student->email)->send(
                new StudentCredentialsMail(
                    studentName: $student->name,
                    studentEmail: $student->email,
                    temporaryPassword: $plainPassword,
                    loginUrl: AppUrl::route('login'),
                )
            );

            EmailLog::query()->create([
                'user_id' => $student->id,
                'email' => $student->email,
                'subject' => $subject,
                'message' => $message,
                'status' => 'sent',
                'email_type' => 'student_credentials',
                'sent_at' => now(),
            ]);

            return true;
        } catch (Throwable $exception) {
            EmailLog::query()->create([
                'user_id' => $student->id,
                'email' => $student->email,
                'subject' => $subject,
                'message' => $message,
                'status' => 'failed',
                'email_type' => 'student_credentials',
                'error_message' => $exception->getMessage(),
                'sent_at' => now(),
                'last_attempt_at' => now(),
            ]);

            return false;
        }
    }

    private function sectionsForDropdown(array $sections): array
    {
        $groupedSections = [];

        foreach ($sections as $section) {
            if (! isset($section['year'], $section['department_id'], $section['letter']) || $section['year'] === null) {
                continue;
            }

            $groupedSections[$section['year']][] = [
                'id' => $section['id'],
                'name' => $section['section_name'],
                'department_id' => $section['department_id'],
                'department_code' => $section['department_code'],
                'letter' => $section['letter'],
            ];
        }

        return $groupedSections;
    }

    private function studentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()
            ->whereHas(
                'role',
                fn ($roleQuery) => $roleQuery->whereIn('role_name', StudentService::manageableRoleNames())
            );
    }

    private function generateTemporaryPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $symbols = '!@#$%';
        $result = '';

        for ($index = 0; $index < max(8, $length - 2); $index += 1) {
            $result .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        $result .= $symbols[random_int(0, strlen($symbols) - 1)];
        $result .= $alphabet[random_int(0, strlen($alphabet) - 1)];

        return Str::of(str_shuffle($result))->substr(0, $length)->value();
    }
}
