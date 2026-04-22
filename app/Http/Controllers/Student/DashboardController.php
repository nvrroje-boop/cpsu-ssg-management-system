<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index(AttendanceService $attendanceService): View
    {
        /** @var User $student */
        $student = Auth::user();

        return view('student.dashboard.index', [
            'student' => [
                'name' => $student->name,
                'email' => $student->email,
            ],
            'attendanceRate' => $attendanceService->getStudentAttendanceRate($student),
            'announcements' => Announcement::query()
                ->published()
                ->visibleToUser($student)
                ->orderByDesc('created_at')
                ->limit(2)
                ->get()
                ->map(fn (Announcement $announcement): array => [
                    'title' => $announcement->title,
                    'status' => ucfirst($announcement->visibility),
                ])
                ->all(),
            'upcomingEvents' => Event::query()
                ->visibleToUser($student)
                ->whereDate('event_date', '>=', today())
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->limit(2)
                ->get()
                ->map(fn (Event $event): array => [
                    'title' => $event->event_title,
                    'schedule' => trim(
                        (optional($event->event_date)?->format('F d, Y') ?? (string) $event->event_date)
                        .' - '
                        .substr((string) $event->event_time, 0, 5)
                    ),
                ])
                ->all(),
        ]);
    }

    public function profile(AttendanceService $attendanceService, QrCodeService $qrCodeService): View
    {
        /** @var User $student */
        $student = Auth::user();
        $student = $qrCodeService->ensureStudentIdentityToken($student);

        return view('student.profile.index', [
            'student' => $student->load(['role', 'department', 'section']),
            'semesterSummary' => $attendanceService->getStudentSemesterSummary($student),
            'studentQrImage' => $qrCodeService->getStudentIdentityQrImage($student),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:30',
            'course' => 'nullable|string|max:100',
        ]);

        /** @var User $student */
        $student = Auth::user();
        $student->update($request->only(['name', 'email', 'phone', 'course']));

        return redirect()
            ->route($this->portalRouteName('profile', $student->isAdmin() ? 'admin' : 'student'))
            ->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'confirmed'],
        ]);

        /** @var User $student */
        $student = Auth::user();
        $student->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()
            ->route($this->portalRouteName('profile', $student->isAdmin() ? 'admin' : ($student->isOfficer() ? 'officer' : 'student')))
            ->with('success', 'Password changed successfully.');
    }
}
