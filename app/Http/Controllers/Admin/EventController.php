<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Jobs\SendEventNotificationEmails;
use App\Jobs\SendEventQrEmails;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Services\EventAttendanceWorkflowService;
use App\Services\QrCodeService;
use App\Services\StudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->with('department')
            ->withCount(['eventQrs', 'attendances'])
            ->orderByDesc('event_date')
            ->orderByDesc('event_time')
            ->get();

        return view('admin.events.index', [
            'events' => $events,
        ]);
    }

    public function show(int $event, EventAttendanceWorkflowService $attendanceWorkflow, QrCodeService $qrCodeService): View
    {
        $record = Event::query()
            ->with(['department', 'creator:id,name'])
            ->withCount(['eventQrs', 'attendances'])
            ->findOrFail($event);

        return view('admin.events.show', [
            'event' => $record,
            'attendanceSummary' => $attendanceWorkflow->eventSummary($record),
            'attendanceRecords' => $attendanceWorkflow->eventAttendances($record, 20),
            'eligibleStudents' => $attendanceWorkflow->eligibleStudentsQuery($record)->get(),
            'eventQrImage' => $record->attendance_active ? $qrCodeService->getEventAttendanceQrImage($record) : '',
            'eventQrLink' => $record->attendance_active ? $qrCodeService->eventAttendanceLink($record) : null,
            'attendancePhase' => $attendanceWorkflow->currentPhase($record),
            'timeInWindow' => $attendanceWorkflow->formatWindow($record, 'time_in'),
            'timeOutWindow' => $attendanceWorkflow->formatWindow($record, 'time_out'),
        ]);
    }

    public function create(StudentService $studentService): View
    {
        return view('admin.events.create', [
            'departments' => $studentService->getDepartments(),
        ]);
    }

    public function store(StoreEventRequest $request, EventAttendanceWorkflowService $attendanceWorkflow): RedirectResponse
    {
        $validated = $attendanceWorkflow->applyScheduleDefaults($request->validated());
        $validated['created_by_user_id'] = $request->user()?->id ?? $validated['created_by_user_id'] ?? 1;
        $validated['attendance_required'] = (bool) ($validated['attendance_required'] ?? true);

        $event = Event::query()->create($validated);

        $this->notifyAudience($event);

        return redirect()
            ->route($this->portalRouteName('events.index'))
            ->with('success', 'Event created successfully.');
    }

    public function edit(int $event, StudentService $studentService): View
    {
        $record = Event::query()
            ->withCount(['eventQrs', 'attendances'])
            ->findOrFail($event);

        return view('admin.events.edit', [
            'event' => $record,
            'departments' => $studentService->getDepartments(),
        ]);
    }

    public function update(StoreEventRequest $request, int $event, EventAttendanceWorkflowService $attendanceWorkflow): RedirectResponse
    {
        $record = Event::query()->findOrFail($event);
        $validated = $attendanceWorkflow->applyScheduleDefaults($request->validated());
        $validated['created_by_user_id'] = $request->user()?->id ?? $record->created_by_user_id;
        $validated['attendance_required'] = (bool) ($validated['attendance_required'] ?? true);

        $record->update($validated);

        return redirect()
            ->route($this->portalRouteName('events.index'))
            ->with('success', 'Event updated successfully.');
    }

    public function resendQrEmails(int $event): RedirectResponse
    {
        $record = Event::query()->findOrFail($event);

        SendEventQrEmails::dispatchSync($record);

        return redirect()
            ->route($this->portalRouteName('events.show'), $record->id)
            ->with('success', 'QR email resend completed successfully.');
    }

    public function destroy(int $event): RedirectResponse
    {
        $record = Event::query()->findOrFail($event);
        EventAttendance::query()->where('event_id', $record->id)->delete();
        $record->delete();

        return redirect()
            ->route($this->portalRouteName('events.index'))
            ->with('success', 'Event removed successfully.');
    }

    private function notifyAudience(Event $event): void
    {
        SendEventNotificationEmails::dispatch($event->id);
    }
}
