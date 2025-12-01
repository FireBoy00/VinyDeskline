<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Desk;

class DeskController extends Controller
{
    // Position thresholds for desk state classification (in millimeters)
    private const SEATED_THRESHOLD = 700;
    private const STANDING_THRESHOLD = 1200;

    public function index()
    {
        $apiKey = env('DESKS_API_KEY');
        $base = env('API_BASE');
        $url = "{$base}/{$apiKey}/desks";

        $response = Http::get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'API error'], 500);
        }

        $deskIds = $response->json();

        foreach ($deskIds as $id) {
            Desk::updateOrCreate(
                ['desk_id' => $id],
            );
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'desks'   => $deskIds
        ]);
    }

    public function state($desk_id)
    {
        $apiKey = env('DESKS_API_KEY');
        $base = env('API_BASE');
        $url = "{$base}/{$apiKey}/desks/{$desk_id}";

        $response = Http::get($url);

        $deskData = $response->json();

        Desk::updateOrCreate(
            ['desk_id' => $desk_id],
            ['state' => $deskData['state']]
        );

        return response()->json($deskData);
    }

    /**
     * Return aggregated statistics about desks (admin view)
     * - total: total desks tracked in DB
     * - occupied / available / raised / lowered / faulty
     */
    public function stats()
    {
        $apiKey = env('DESKS_API_KEY');
        $base = env('API_BASE');
        $listUrl = "{$base}/{$apiKey}/desks";

        // Initialize counters
        $counts = [
            'seated'   => 0,
            'standing' => 0,
            'active'   => 0,
            'cleaning' => 0,
            'idle'     => 0,
        ];

        $totalUsers = 0;

        try {
            $res = Http::timeout(5)->get($listUrl);

            if ($res->failed()) {
                Log::warning('DeskController::stats - failed to fetch desk list', ['url' => $listUrl, 'status' => $res->status()]);
                throw new \Exception("Failed to fetch desk list (HTTP {$res->status()}): {$res->body()}");
            }

            $ids = $res->json();

            if (!is_array($ids)) {
                Log::warning('DeskController::stats - desk list not array', ['body' => $res->body()]);
                throw new \Exception('Invalid desk list format');
            }

            // For each desk id, fetch the detailed desk object
            foreach ($ids as $deskId) {
                try {
                    $deskUrl = "{$base}/{$apiKey}/desks/{$deskId}";
                    $dres = Http::timeout(5)->get($deskUrl);

                    if ($dres->failed()) {
                        Log::warning('DeskController::stats - failed to fetch desk', ['desk' => $deskId, 'status' => $dres->status()]);
                        // treat as idle/missing; continue
                        $counts['idle']++;
                        continue;
                    }

                    $deskJson = $dres->json();

                    // Some simulator variants return the structure directly, others nest under top-level key.
                    // Accept several shapes:
                    // 1) {"config":..., "state":..., "usage":..., "lastErrors":... , "user":"seated"}  <-- unlikely for single-desk endpoint
                    // 2) {"desk_data": {...}, "user":"seated"}  <-- if your simulator returns this
                    // 3) {"desk_data": {...}}  <-- maybe no user
                    // So try to detect user:
                    $user = null;
                    if (isset($deskJson['user'])) {
                        $user = strtolower(trim($deskJson['user'] ?? ''));
                    } elseif (isset($deskJson['desk_data']) && isset($deskJson['desk_data']['user'])) {
                        $user = strtolower(trim($deskJson['desk_data']['user'] ?? ''));
                    } elseif (isset($deskJson['desk_data']) && isset($deskJson['desk_data']['state']['user'])) {
                        $user = strtolower(trim($deskJson['desk_data']['state']['user'] ?? ''));
                    }

                    // Many simulator variants don't include `user` per-desk; some include "user" at top-level response.
                    // If not found, try to infer by position_mm and status (best-effort).
                    if (!$user) {
                        $position = $deskJson['desk_data']['state']['position_mm'] ?? $deskJson['state']['position_mm'] ?? null;
                        $status = strtolower($deskJson['desk_data']['state']['status'] ?? $deskJson['state']['status'] ?? '');
                        // If status indicates collision -> treat as active (faulty)
                        if (str_contains($status, 'collision') || ($deskJson['desk_data']['state']['isAntiCollision'] ?? $deskJson['state']['isAntiCollision'] ?? false)) {
                            $user = 'active';
                        } elseif ($position !== null) {
                            // heuristic: low => seated, high => standing, middle => active/idle
                            if ($position <= self::SEATED_THRESHOLD) $user = 'seated';
                            elseif ($position >= self::STANDING_THRESHOLD) $user = 'standing';
                            else $user = 'idle';
                        } else {
                            $user = 'idle';
                        }
                    }

                    if (isset($counts[$user])) {
                        $counts[$user]++;
                    } else {
                        // Unknown state -> classify as idle
                        $counts['idle']++;
                    }

                    $totalUsers++;

                } catch (\Exception $e) {
                    // per-desk failure: log and count as idle
                    Log::warning('DeskController::stats - exception fetching desk', ['desk' => $deskId, 'err' => $e->getMessage()]);
                    $counts['idle']++;
                }
            }

        } catch (\Exception $e) {
            // If API failed entirely, fall back to local DB (Desk::all()) if you have stored state there
            Log::warning('DeskController::stats - falling back to DB due to API error', ['err' => $e->getMessage()]);

            $desks = Desk::all();
            foreach ($desks as $desk) {
                $state = $desk->state ?? [];
                // try to read previously stored 'user' field if saved, else infer from position/status
                $user = strtolower(trim($state['user'] ?? ($state['status'] ?? 'idle')));
                if (!isset($counts[$user])) {
                    // infer from position
                    $position = $state['position_mm'] ?? null;
                    if ($position !== null) {
                        if ($position <= self::SEATED_THRESHOLD) $user = 'seated';
                        elseif ($position >= self::STANDING_THRESHOLD) $user = 'standing';
                        else $user = 'idle';
                    } else {
                        $user = 'idle';
                    }
                }
                $counts[$user] = ($counts[$user] ?? 0) + 1;
                $totalUsers++;
            }
        }

        // Return normalized response for frontend
        return response()->json([
            'total_users' => $totalUsers,
            'seated'      => $counts['seated'],
            'standing'    => $counts['standing'],
            'active'      => $counts['active'],
            'cleaning'    => $counts['cleaning'],
            'idle'        => $counts['idle'],
            'last_updated' => now()->toIso8601String(),
        ]);
    }

}
