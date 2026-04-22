<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EventController extends Controller
{
    /**
     * Get real-time attendance for an event
     */
    public function getAttendance(Request $request, $id): JsonResponse
    {
        $event = Event::with('eventAttendances.user')->find($id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        return response()->json([
            'event' => [
                'id' => $event->id,
                'title' => $event->event_title,
                'total_capacity' => null,
                'attendance_count' => $event->eventAttendances()->count(),
            ],
            'attendance' => $event->eventAttendances()
                ->with('user')
                ->orderByDesc('last_scanned_at')
                ->get()
                ->map(fn($a) => [
                    'id' => $a->id,
                    'student_id' => $a->user?->student_number,
                    'name' => $a->user?->name,
                    'timestamp' => ($a->last_scanned_at ?? $a->time_in)?->format('H:i:s'),
                    'course' => $a->user?->course,
                    'section' => $a->user?->section?->section_name,
                    'status' => $a->status,
                ])->toArray(),
        ]);
    }

    /**
     * Export attendance as CSV
     */
    public function exportAttendance(Request $request, $id): StreamedResponse
    {
        $event = Event::with('eventAttendances.user')->find($id);

        if (!$event) {
            abort(404, 'Event not found');
        }

        $response = new StreamedResponse(function () use ($event) {
            $handle = fopen('php://output', 'w');

            // Headers
            fputcsv($handle, ['Student ID', 'Name', 'Course', 'Section', 'Last Scan', 'Status']);

            // Data
            foreach ($event->eventAttendances()->with('user')->get() as $attendance) {
                fputcsv($handle, [
                    $attendance->user?->student_number,
                    $attendance->user?->name,
                    $attendance->user?->course,
                    $attendance->user?->section?->section_name,
                    ($attendance->last_scanned_at ?? $attendance->time_in)?->format('Y-m-d H:i:s'),
                    $attendance->status,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $event->event_title . '-attendance.csv"');

        return $response;
    }
}
