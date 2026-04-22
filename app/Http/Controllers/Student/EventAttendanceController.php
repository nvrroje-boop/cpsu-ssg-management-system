<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventAttendanceWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EventAttendanceController extends Controller
{
    public function __construct(
        private readonly EventAttendanceWorkflowService $attendanceWorkflow,
    ) {
    }

    public function selfScan(Request $request, Event $event): RedirectResponse
    {
        $result = $this->attendanceWorkflow->recordSelfScan(
            $event,
            $request->user(),
            $request->string('token')->toString(),
            $request->input('expires'),
        );

        return redirect()
            ->route('student.events.show', $event->id)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
