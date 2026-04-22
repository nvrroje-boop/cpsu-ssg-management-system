<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private readonly SystemNotificationService $notificationService,
    ) {
    }

    public function index(Request $request): JsonResponse|View
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $notifications = $this->notificationService->latestForUser($user, $request->boolean('all') ? 50 : 5);
        $unreadCount = $this->notificationService->unreadCountForUser($user);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'unread' => $unreadCount,
                'notifications' => $notifications->map(fn (SystemNotification $notification): array => $this->transformNotification($notification))->all(),
            ]);
        }

        return view('notifications.index', [
            'notifications' => $this->notificationService->queryForUser($user)->limit(50)->get(),
            'unreadCount' => $unreadCount,
        ]);
    }

    public function markRead(Request $request, SystemNotification $notification): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        if (! $this->notificationService->markRead($user, $notification)) {
            abort(404);
        }

        return response()->json([
            'success' => true,
            'unread' => $this->notificationService->unreadCountForUser($user),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $this->notificationService->markAllRead($user);

        return response()->json([
            'success' => true,
            'unread' => 0,
        ]);
    }

    private function transformNotification(SystemNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'type' => $notification->type,
            'link' => $notification->link,
            'read' => $notification->read_at !== null,
            'time' => $notification->created_at?->diffForHumans() ?? 'Just now',
        ];
    }
}
