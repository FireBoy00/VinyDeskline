<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Desk;
use App\Models\User;
use App\Models\DeskMetric;
use App\Services\DeskApiService;
use Illuminate\Http\Request;

class DeskController extends Controller
{
    // Position thresholds for desk state classification (in millimeters)
    private const SEATED_THRESHOLD = 700;
    private const STANDING_THRESHOLD = 1200;

    protected $deskApiService;

    public function __construct(DeskApiService $deskApiService)
    {
        $this->deskApiService = $deskApiService;
    }

    /**
     * Get all desks with their assignments and locations
     */
    public function index()
    {
        $desks = Desk::with(['user', 'room', 'floor'])
            ->where('is_removed_from_api', false)
            ->get();

        return response()->json([
            'success' => true,
            'desks' => $desks
        ]);
    }

    /**
     * Get specific desk details
     */
    public function show($deskId)
    {
        $desk = Desk::with(['user', 'room.floor', 'floor'])
            ->where('desk_id', $deskId)
            ->first();

        if (!$desk) {
            return response()->json([
                'success' => false,
                'message' => 'Desk not found'
            ], 404);
        }

        // Get real-time data from API
        $apiData = $this->deskApiService->getDeskData($deskId);

        return response()->json([
            'success' => true,
            'desk' => $desk,
            'api_data' => $apiData
        ]);
    }

    /**
     * Update desk height via API
     */
    public function setHeight(Request $request, $deskId)
    {
        $request->validate([
            'position_mm' => 'required|integer|min=500|max=1300'
        ]);

        $targetHeight = $request->input('position_mm');

        // Send update to API
        $success = $this->deskApiService->updateDeskPosition($deskId, $targetHeight);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update desk height'
            ], 500);
        }

        // Update local database
        $desk = Desk::where('desk_id', $deskId)->first();
        if ($desk) {
            $desk->update(['position_mm' => $targetHeight]);
        }

        return response()->json([
            'success' => true,
            'position_mm' => $targetHeight
        ]);
    }

    /**
     * Assign user to desk
     */
    public function assignUser(Request $request, $deskId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $desk = Desk::where('desk_id', $deskId)->first();

        if (!$desk) {
            return response()->json([
                'success' => false,
                'message' => 'Desk not found'
            ], 404);
        }

        // Check if another user is already assigned to this desk
        $existingUser = User::where('desk_id', $deskId)
            ->where('id', '!=', $request->user_id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This desk is already assigned to another user'
            ], 422);
        }

        // Check if user is already assigned to another desk
        $user = User::find($request->user_id);
        if ($user->desk_id && $user->desk_id !== $deskId) {
            // Unassign from old desk first
            $user->update(['desk_id' => null]);
        }

        // Assign user to desk
        $user->update(['desk_id' => $deskId]);

        return response()->json([
            'success' => true,
            'message' => 'User assigned successfully',
            'user' => $user->load('desk')
        ]);
    }

    /**
     * Unassign user from desk
     */
    public function unassignUser($deskId)
    {
        $user = User::where('desk_id', $deskId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No user assigned to this desk'
            ], 404);
        }

        $user->update(['desk_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'User unassigned successfully'
        ]);
    }

    /**
     * Get desk metrics/statistics
     */
    public function getMetrics($deskId, Request $request)
    {
        $days = $request->input('days', 7); // Default to last 7 days

        $metrics = DeskMetric::where('desk_id', $deskId)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Calculate sitting/standing time
        $sittingMinutes = 0;
        $standingMinutes = 0;

        for ($i = 0; $i < $metrics->count() - 1; $i++) {
            $current = $metrics[$i];
            $next = $metrics[$i + 1];

            $minutesDiff = $current->recorded_at->diffInMinutes($next->recorded_at);

            if ($current->is_sitting) {
                $sittingMinutes += $minutesDiff;
            } else {
                $standingMinutes += $minutesDiff;
            }
        }

        return response()->json([
            'success' => true,
            'desk_id' => $deskId,
            'period_days' => $days,
            'sitting_minutes' => $sittingMinutes,
            'standing_minutes' => $standingMinutes,
            'total_minutes' => $sittingMinutes + $standingMinutes,
            'metrics' => $metrics
        ]);
    }

    /**
     * Return aggregated statistics about desks (admin view)
     */
    public function stats()
    {
        $desks = Desk::with('user')
            ->where('is_removed_from_api', false)
            ->get();

        $counts = [
            'total' => $desks->count(),
            'assigned' => 0,
            'unassigned' => 0,
            'seated' => 0,
            'standing' => 0,
            'idle' => 0,
        ];

        foreach ($desks as $desk) {
            if ($desk->user) {
                $counts['assigned']++;

                // Determine state based on position
                if ($desk->position_mm !== null) {
                    if ($desk->position_mm <= self::SEATED_THRESHOLD) {
                        $counts['seated']++;
                    } elseif ($desk->position_mm >= self::STANDING_THRESHOLD) {
                        $counts['standing']++;
                    } else {
                        $counts['idle']++;
                    }
                } else {
                    $counts['idle']++;
                }
            } else {
                $counts['unassigned']++;
            }
        }

        return response()->json([
            'success' => true,
            'stats' => $counts,
            'last_updated' => now()->toIso8601String(),
        ]);
    }
}
