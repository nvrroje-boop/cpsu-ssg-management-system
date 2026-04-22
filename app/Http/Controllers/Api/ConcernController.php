<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Concern;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConcernController extends Controller
{
    /**
     * Add a reply to a concern
     */
    public function addReply(Request $request, $id): JsonResponse
    {
        $concern = Concern::find($id);

        if (!$concern) {
            return response()->json(['error' => 'Concern not found'], 404);
        }

        // Check if user is admin or officer
        if (!$request->user()->hasRole(['admin', 'officer'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
            'status' => 'nullable|in:pending,resolved',
        ]);

        // Update concern with reply
        $concern->update([
            'reply_message' => $request->message,
            'replied_by_user_id' => $request->user()->id,
            'replied_at' => now(),
            'status' => $request->status ?? 'resolved',
        ]);

        return response()->json([
            'success' => true,
            'concern' => [
                'id' => $concern->id,
                'message' => $concern->reply_message,
                'user' => $concern->replier?->name,
                'created_at' => $concern->replied_at?->diffForHumans(),
            ],
        ]);
    }

    /**
     * Get all replies for a concern (returns the concern with its reply)
     */
    public function getReplies(Request $request, $id): JsonResponse
    {
        $concern = Concern::find($id);

        if (!$concern) {
            return response()->json(['error' => 'Concern not found'], 404);
        }

        $replies = [];
        if ($concern->reply_message) {
            $replies[] = [
                'id' => $concern->id,
                'message' => $concern->reply_message,
                'user' => $concern->replier?->name ?? 'Unknown',
                'role' => $concern->replier?->roles->first()?->name,
                'created_at' => $concern->replied_at?->diffForHumans(),
            ];
        }

        return response()->json([
            'concern' => [
                'id' => $concern->id,
                'title' => $concern->title,
                'message' => $concern->description,
                'status' => $concern->status,
                'created_at' => $concern->created_at->format('M d, Y'),
            ],
            'replies' => $replies,
        ]);
    }
}
