<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Jobs\SendAnnouncementEmails;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\StudentFilterService;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $view = request()->query('tab', 'active');

        $query = Announcement::query()
            ->with(['department', 'creator:id,name', 'notifications']);

        if ($view === 'archived') {
            $query->archived();
        } else {
            $query->notArchived();
        }

        $announcements = $query
            ->orderByDesc('created_at')
            ->paginate(15);

        $announcements->each(fn ($announcement) => $announcement->stats = $announcement->getStats());

        $archivedCount = Announcement::archived()->count();

        return view('admin.announcements.index', [
            'announcements' => $announcements,
            'currentTab' => $view,
            'archivedCount' => $archivedCount,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load(['department', 'creator:id,name', 'notifications.student:id,email,name']);

        $notificationsQuery = $announcement->notifications()->with('student:id,email,name,student_number');

        if (request()->filled('status')) {
            $notificationsQuery->where('status', request()->string('status')->toString());
        }

        $targetPreviewCount = $announcement->status === 'draft'
            ? $announcement->getTargetStudents()->count()
            : max((int) $announcement->total_recipients, $announcement->getTargetStudents()->count());

        return view('admin.announcements.show', [
            'announcement' => $announcement,
            'notifications' => $notificationsQuery->paginate(20)->withQueryString(),
            'stats' => array_merge($announcement->getStats(), [
                'target_preview_count' => $targetPreviewCount,
            ]),
            'filterSummary' => $this->filterSummary($announcement),
            'groupedByStatus' => $announcement->notifications()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.create', [
            'departments' => Department::query()->orderBy('department_name')->get(['id', 'department_name']),
            'sections' => Section::query()->orderBy('section_name')->get(['id', 'section_name', 'department_id', 'year_level']),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $announcement = Announcement::query()->create($this->payloadFromRequest($request));

        return redirect()
            ->route($this->portalRouteName('announcements.edit'), $announcement->id)
            ->with('success', 'Announcement created as draft.');
    }

    public function edit(int $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => Announcement::query()->findOrFail($announcement),
            'departments' => Department::query()->orderBy('department_name')->get(['id', 'department_name']),
            'sections' => Section::query()->orderBy('section_name')->get(['id', 'section_name', 'department_id', 'year_level']),
        ]);
    }

    public function update(StoreAnnouncementRequest $request, int $announcement): RedirectResponse
    {
        $record = Announcement::query()->findOrFail($announcement);

        if ($record->isArchived()) {
            return back()->with('error', 'Cannot edit archived announcements.');
        }

        $record->update($this->payloadFromRequest($request, $record));

        return back()->with('success', 'Announcement updated successfully.');
    }

    public function preview(Announcement $announcement): View
    {
        return view('admin.announcements.preview', [
            'announcement' => $announcement,
            'totalRecipients' => $announcement->getTargetStudents()->count(),
        ]);
    }

    public function send(Request $request, Announcement $announcement, StudentFilterService $filterService): RedirectResponse
    {
        if ($announcement->status !== 'draft') {
            return back()->with('error', 'This announcement has already been sent.');
        }

        $validated = $request->validate([
            'send_type' => ['required', 'in:now,scheduled'],
            'send_at' => ['nullable', 'required_if:send_type,scheduled', 'date', 'after:now'],
        ]);

        $filters = $announcement->target_filters ?? [];
        $departmentId = isset($filters['department_id']) ? (int) $filters['department_id'] : null;
        $year = isset($filters['year']) ? (int) $filters['year'] : null;
        $sectionId = isset($filters['section_id']) ? (int) $filters['section_id'] : null;
        $matchedStudents = $filterService->getFilteredStudentsCount($departmentId, $year, $sectionId);

        if ($matchedStudents === 0) {
            return back()->with('error', 'No students match the selected department and year filters.');
        }

        try {
            DB::transaction(function () use ($announcement, $validated, $matchedStudents): void {
                $sendAt = $validated['send_type'] === 'now'
                    ? now()
                    : Carbon::parse($validated['send_at']);

                $announcement->update([
                    'send_at' => $sendAt,
                    'sent_at' => $validated['send_type'] === 'now' ? now() : null,
                    'status' => $validated['send_type'] === 'now' ? 'sent' : 'scheduled',
                    'total_recipients' => $matchedStudents,
                    'sent_count' => 0,
                    'failed_count' => 0,
                ]);
            });

        if ($validated['send_type'] === 'scheduled') {
            return redirect()
                ->route($this->portalRouteName('announcements.index'))
                ->with('success', 'Announcement scheduled for '.$announcement->send_at?->format('M d, Y h:i A'));
        }

        SendAnnouncementEmails::dispatch($announcement->id);

            return redirect()
                ->route($this->portalRouteName('announcements.show'), $announcement->id)
                ->with('success', "Announcement send has been queued for {$matchedStudents} student(s).");
        } catch (\Throwable $exception) {
            return back()->with('error', 'Failed to send announcement: '.$exception->getMessage());
        }
    }

    public function resendFailed(Announcement $announcement): RedirectResponse
    {
        $failedCount = $announcement->notifications()
            ->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->count();

        if ($failedCount === 0) {
            return back()->with('info', 'No failed notifications to resend.');
        }

        $announcement->notifications()
            ->where('status', 'failed')
            ->where('retry_count', '<', 3)
            ->update([
                'status' => 'queued',
                'error_message' => null,
                'last_attempt_at' => now(),
            ]);

        SendAnnouncementEmails::dispatch($announcement->id, true);

        return back()->with('success', "Queued {$failedCount} failed notification(s) for resend.");
    }

    public function getTargetPreview(Request $request, StudentFilterService $filterService)
    {
        $filters = $request->validate([
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'year' => ['nullable', 'integer', 'in:1,2,3,4'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
        ]);

        $departmentId = isset($filters['department_id']) ? (int) $filters['department_id'] : null;
        $year = isset($filters['year']) ? (int) $filters['year'] : null;
        $sectionId = isset($filters['section_id']) ? (int) $filters['section_id'] : null;

        $students = $filterService->getPreviewStudents($departmentId, $year, $sectionId);

        return response()->json([
            'count' => $filterService->getFilteredStudentsCount($departmentId, $year, $sectionId),
            'students' => $students->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'student_number' => $student->student_number,
                'email' => $student->email,
                'department' => $student->department?->department_name,
                'section' => $student->section?->section_name,
            ])->values(),
        ]);
    }

    public function destroy(int $announcement): RedirectResponse
    {
        $record = Announcement::query()->findOrFail($announcement);

        if ($record->isArchived()) {
            return back()->with('error', 'This announcement is already archived.');
        }

        $record->notifications()->delete();
        $record->delete();

        return redirect()
            ->route($this->portalRouteName('announcements.index'))
            ->with('success', 'Announcement removed successfully.');
    }

    public function archive(int $announcement): RedirectResponse
    {
        $record = Announcement::query()->findOrFail($announcement);

        if ($record->isArchived()) {
            return back()->with('info', 'This announcement is already archived.');
        }

        $record->archive();

        return back()->with('success', 'Announcement archived successfully.');
    }

    public function unarchive(int $announcement): RedirectResponse
    {
        $record = Announcement::query()->withTrashed()->findOrFail($announcement);

        if (! $record->isArchived()) {
            return back()->with('info', 'This announcement is not archived.');
        }

        $record->unarchive();

        return back()->with('success', 'Announcement restored from archive.');
    }

    private function payloadFromRequest(StoreAnnouncementRequest $request, ?Announcement $announcement = null): array
    {
        $validated = $request->validated();
        $filterDepartmentId = isset($validated['filter_department_id']) ? (int) $validated['filter_department_id'] : null;

        return [
            'title' => $validated['title'],
            'message' => $validated['message'],
            'description' => $validated['description'] ?? Str::limit(strip_tags($validated['message']), 255),
            'visibility' => $validated['visibility'] ?? ($filterDepartmentId ? 'private' : 'public'),
            'department_id' => $validated['department_id'] ?? $filterDepartmentId,
            'created_by_user_id' => $request->user()?->id ?? $announcement?->created_by_user_id,
            'status' => $announcement?->status ?? 'draft',
            'target_filters' => [
                'year' => $validated['filter_year'] ?? null,
                'department_id' => $filterDepartmentId,
                'section_id' => isset($validated['filter_section_id']) ? (int) $validated['filter_section_id'] : null,
            ],
        ];
    }

    private function filterSummary(Announcement $announcement): array
    {
        $filters = $announcement->target_filters ?? [];

        return array_filter([
            'Department' => isset($filters['department_id'])
                ? Department::query()->find($filters['department_id'])?->department_name
                : null,
            'Year Level' => isset($filters['year']) ? 'Year '.$filters['year'] : null,
            'Section' => isset($filters['section_id'])
                ? Section::query()->find($filters['section_id'])?->section_name
                : null,
        ]);
    }
}
