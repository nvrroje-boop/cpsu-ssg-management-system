<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventAttendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    /**
     * Process QR code scan and mark attendance
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => 'required|exists:event_qrs,token',
        ]);

        $qrCode = \App\Models\EventQr::where('token', $request->qr_token)->first();
        $user = $request->user();

        // Check if already attended
        $existing = EventAttendance::where('user_id', $user->id)
            ->where('event_id', $qrCode->event_id)
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already marked attendance for this event',
            ], 400);
        }

        // Create attendance record
        $attendance = EventAttendance::create([
            'user_id' => $user->id,
            'event_id' => $qrCode->event_id,
            'event_qr_id' => $qrCode->id,
            'scanned_at' => now(),
        ]);

        $event = Event::find($qrCode->event_id);

        return response()->json([
            'success' => true,
            'message' => "Attendance marked for {$event->title}",
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'location' => $event->location,
                'attendanceCount' => $event->eventAttendances()->count(),
            ],
        ]);
    }
}
