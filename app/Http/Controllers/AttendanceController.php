<?php

namespace App\Http\Controllers;

use App\Models\EventAttendance;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function scan(Request $request, string $token, QrCodeService $qrCodeService): JsonResponse|RedirectResponse
    {
        $eventQr = $qrCodeService->validateQrToken($token);

        if ($eventQr === null) {
            return $this->failureResponse($request, 'This QR code is invalid, expired, or has already been used.', 404);
        }

        $duplicateAttendance = EventAttendance::query()
            ->where('event_id', $eventQr->event_id)
            ->where('student_id', $eventQr->user_id)
            ->with(['event:id,event_title', 'student:id,name'])
            ->first();

        if ($duplicateAttendance !== null) {
            return $this->failureResponse(
                $request,
                sprintf(
                    '%s has already been marked present for %s.',
                    $duplicateAttendance->student?->name ?? 'This student',
                    $duplicateAttendance->event?->event_title ?? 'this event',
                ),
                409,
                true,
            );
        }

        try {
            DB::transaction(function () use ($eventQr, $request): void {
                EventAttendance::query()->create([
                    'event_id' => $eventQr->event_id,
                    'student_id' => $eventQr->user_id,
                    'token' => $eventQr->token,
                    'scanned_by_user_id' => $request->user()?->id,
                    'scanned_at' => now(),
                ]);

                $eventQr->markAsUsed();
            });
        } catch (\Throwable $exception) {
            Log::error('Attendance scan failed', [
                'token' => $token,
                'event_id' => $eventQr->event_id,
                'student_id' => $eventQr->user_id,
                'message' => $exception->getMessage(),
            ]);

            return $this->failureResponse($request, 'Attendance could not be recorded. Please try again.', 500);
        }

        $message = sprintf(
            'Attendance recorded for %s in %s.',
            $eventQr->user?->name ?? 'the student',
            $eventQr->event?->event_title ?? 'the selected event',
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'event' => $eventQr->event?->event_title,
                'student' => $eventQr->user?->name,
            ]);
        }

        return back()->with('success', $message);
    }

    public function showQr(string $token, QrCodeService $qrCodeService): View
    {
        $eventQr = $qrCodeService->validateQrToken($token);

        abort_if($eventQr === null, 404, 'This QR code is invalid, expired, or has already been used.');

        return view('attendance.qr', [
            'eventQr' => $eventQr,
            'imageUrl' => route('attendance.qr.image', ['token' => $token]),
            'downloadUrl' => route('attendance.qr.download', ['token' => $token]),
        ]);
    }

    public function qrImage(string $token, QrCodeService $qrCodeService): Response
    {
        $eventQr = $qrCodeService->validateQrToken($token);

        abort_if($eventQr === null, 404, 'This QR code is invalid, expired, or has already been used.');

        $downloadAsset = $qrCodeService->getQrDownloadableImageForEventQr($eventQr);

        abort_if(($downloadAsset['binary'] ?? '') === '' || ($downloadAsset['mime'] ?? '') === '', 404, 'QR image unavailable.');

        return response($downloadAsset['binary'], 200, [
            'Content-Type' => $downloadAsset['mime'],
            'Cache-Control' => 'private, max-age=300',
            'Content-Disposition' => 'inline; filename="attendance-qr.'.$downloadAsset['extension'].'"',
        ]);
    }

    public function downloadQr(string $token, QrCodeService $qrCodeService): Response
    {
        $eventQr = $qrCodeService->validateQrToken($token);

        abort_if($eventQr === null, 404, 'This QR code is invalid, expired, or has already been used.');

        $downloadAsset = $qrCodeService->getQrDownloadableImageForEventQr($eventQr);

        abort_if(($downloadAsset['binary'] ?? '') === '' || ($downloadAsset['mime'] ?? '') === '', 404, 'QR image unavailable.');

        return response($downloadAsset['binary'], 200, [
            'Content-Type' => $downloadAsset['mime'],
            'Content-Disposition' => 'attachment; filename="attendance-qr-'.$eventQr->user_id.'-'.$eventQr->event_id.'.'.$downloadAsset['extension'].'"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function failureResponse(Request $request, string $message, int $status, bool $duplicate = false): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'duplicate' => $duplicate,
            ], $status);
        }

        return back()->with($duplicate ? 'status' : 'error', $message);
    }
}
