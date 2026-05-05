<?php

namespace App\Http\Controllers;

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
    public function getNotifications(): JsonResponse
    {
        return response()->json($this->topbarData->notifications());
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
}
