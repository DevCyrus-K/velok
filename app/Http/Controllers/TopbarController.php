<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Support\TopbarData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopbarController extends Controller
{
    public function __construct(private readonly TopbarData $topbarData)
    {
    }

    /**
     * Get unread messages count
     */
    public function getNotifications(Request $request): JsonResponse
    {
        return response()->json($this->topbarData->notifications($request->user()));
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json($this->topbarData->user($user));
    }

    /**
     * Get topbar data (user + notifications)
     */
    public function getTopbarData(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        return response()->json($this->topbarData->forUser($user));
    }

    public function markNotificationRead(Request $request, ActivityNotification $notification): JsonResponse
    {
        abort_unless($this->canManageNotification($request, $notification), 404);

        $notification->markAsRead();
        $this->topbarData->forgetNotifications();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notifications' => $this->topbarData->notifications($request->user()),
        ]);
    }

    public function markAllNotificationsRead(Request $request): JsonResponse
    {
        $userId = $request->user()?->getAuthIdentifier();

        ActivityNotification::query()
            ->whereNull('read_at')
            ->where(function ($query) use ($userId): void {
                $query->whereNull('user_id');

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        $this->topbarData->forgetNotifications();

        return response()->json([
            'message' => 'Notifications marked as read.',
            'notifications' => $this->topbarData->notifications($request->user()),
        ]);
    }

    private function canManageNotification(Request $request, ActivityNotification $notification): bool
    {
        $userId = $request->user()?->getAuthIdentifier();

        return $notification->user_id === null
            || (string) $notification->user_id === (string) $userId;
    }
}
