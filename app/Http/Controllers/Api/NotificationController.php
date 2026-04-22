<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Services\SystemNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private readonly SystemNotificationService $notificationService,
    ) {
    }

    /**
     * Get notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $this->notificationService->latestForUser($user, 20);

        return response()->json([
            'data' => $notifications->map(fn($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'link' => $n->link,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
            'unread_count' => $this->notificationService->unreadCountForUser($user),
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markRead(Request $request, $id): JsonResponse
    {
        $notification = SystemNotification::find($id);

        if (!$notification || ! $this->notificationService->canView($request->user(), $notification)) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $this->notificationService->markRead($request->user(), $notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllRead($request->user());

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
