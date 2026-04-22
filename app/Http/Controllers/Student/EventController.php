<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(): View
    {
        /** @var User $student */
        $student = Auth::user();
        $attendedEventIds = EventAttendance::query()
            ->where('user_id', $student->id)
            ->pluck('event_id')
            ->all();

        $events = Event::query()
            ->visibleToUser($student)
            ->orderBy('event_date')
            ->orderBy('event_time')
            ->get()
            ->map(function (Event $event) use ($attendedEventIds): array {
                return [
                    'id' => $event->id,
                    'event_title' => $event->event_title,
                    'location' => $event->location,
                    'event_date' => optional($event->event_date)?->format('Y-m-d') ?? $event->event_date,
                    'event_time' => substr((string) $event->event_time, 0, 5),
                    'attendance_required' => (bool) $event->attendance_required,
                    'has_attended' => in_array($event->id, $attendedEventIds, true),
                ];
            })
            ->all();

        return view('student.events.index', [
            'events' => $events,
        ]);
    }

    public function show(int $event, QrCodeService $qrCodeService): View
    {
        /** @var User $student */
        $student = Auth::user();
        $student = $qrCodeService->ensureStudentIdentityToken($student);

        $record = Event::query()
            ->visibleToUser($student)
            ->findOrFail($event);

        $attendanceRecord = EventAttendance::query()
            ->where('event_id', $record->id)
            ->where('user_id', $student->id)
            ->first();

        return view('student.events.show', [
            'event' => $record,
            'has_attended' => $attendanceRecord !== null && $attendanceRecord->time_out !== null,
            'attendanceRecord' => $attendanceRecord,
            'studentQrImage' => $qrCodeService->getStudentIdentityQrImage($student),
        ]);
    }

    public function attend(int $event): RedirectResponse
    {
        $eventRecord = Event::query()->findOrFail($event);

        return redirect()
            ->route('student.events.show', $eventRecord)
            ->with('info', 'Present your student QR at the attendance kiosk, or self-scan the event QR if it is available on site.');
    }
}
