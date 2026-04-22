<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\SystemNotification;
use App\Models\User;
use App\Services\EventAttendanceWorkflowService;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventAttendanceController extends Controller
{
    public function __construct(
        private readonly EventAttendanceWorkflowService $attendanceWorkflow,
        private readonly SystemNotificationService $notificationService,
    ) {
    }

    public function start(Request $request, Event $event): RedirectResponse
    {
        $this->attendanceWorkflow->startSession($event, $request->user());

        return back()->with('success', 'Attendance session started.');
    }

    public function stop(Request $request, Event $event): RedirectResponse
    {
        $this->attendanceWorkflow->stopSession($event, $request->user());

        return back()->with('success', 'Attendance session stopped.');
    }

    public function extend(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'window' => ['nullable', 'in:time_in,time_out'],
        ]);

        $this->attendanceWorkflow->extendSession($event, (int) $validated['minutes'], $validated['window'] ?? null);

        return back()->with('success', 'Attendance window extended.');
    }

    public function scan(Request $request, Event $event): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'student_qr' => ['required', 'string', 'max:1000'],
        ]);

        $result = $this->attendanceWorkflow->recordKioskScan($event, $validated['student_qr'], $request->user());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($result, $result['status']);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function upsert(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['required', 'in:present,late,incomplete,absent'],
            'time_in' => ['nullable', 'date'],
            'time_out' => ['nullable', 'date', 'after_or_equal:time_in'],
        ]);

        $student = User::query()->findOrFail($validated['user_id']);

        $this->attendanceWorkflow->upsertManualAttendance($event, $student, $request->user(), $validated);

        return back()->with('success', 'Attendance record saved.');
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => $this->notificationService->latestForUser($user, 5)->map(
                fn (SystemNotification $notification): array => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'read' => $notification->read_at !== null,
                    'time' => $notification->created_at?->diffForHumans() ?? 'Just now',
                ]
            )->all(),
            'unread' => $this->notificationService->unreadCountForUser($user),
        ]);
    }
}
