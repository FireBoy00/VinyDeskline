<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Desk; 


class DeskController extends Controller
{
    public function index()
    {
        $apiKey = env('DESKS_API_KEY');
        // TODO: Move URL to .env
        $url = "http://127.0.0.1:8001/api/v2/{$apiKey}/desks";

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
        // TODO: Move URL to .env
        $url = "http://127.0.0.1:8001/api/v2/{$apiKey}/desks/{$desk_id}";

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
        $total = Desk::count();

        // If the DB has no desks yet, return zeros — admin frontend can trigger a refresh if needed
        if ($total === 0) {
            return response()->json([
                'total' => 0,
                'occupied' => 0,
                'available' => 0,
                'raised' => 0,
                'lowered' => 0,
                'faulty' => 0,
            ]);
        }

        $statusCounts = ['occupied' => 0, 'available' => 0, 'faulty' => 0, 'unknown' => 0];
        $positionCounts = ['raised' => 0, 'lowered' => 0, 'normal' => 0, 'unknown' => 0];

        $desks = Desk::all();

        foreach ($desks as $desk) {
            $state = $desk->state ?? [];

            $status = isset($state['status']) ? strtolower($state['status']) : null;
            $position = isset($state['position_mm']) ? (float) $state['position_mm'] : null;

            // Determine mutually-exclusive status bucket (priority: faulty -> occupied -> available -> unknown)
            if ($status !== null && (str_contains($status, 'collision') || str_contains($status, 'error') || str_contains($status, 'faulty'))) {
                $statusCounts['faulty']++;
            } elseif ($status !== null && (str_contains($status, 'moving') || str_contains($status, 'occupied') || str_contains($status, 'in use'))) {
                $statusCounts['occupied']++;
            } elseif ($status === null || $status === '' || $status === 'normal' || $status === 'available') {
                $statusCounts['available']++;
            } else {
                $statusCounts['unknown']++;
            }

            // Determine position bucket (mutually exclusive)
            if ($position === null) {
                $positionCounts['unknown']++;
            } else if ($position >= 1200) {
                $positionCounts['raised']++;
            } else if ($position <= 700) {
                $positionCounts['lowered']++;
            } else {
                $positionCounts['normal']++;
            }
        }

        // Calculate top-level counts (backwards-compatible) but ensure they are derived from the deterministic buckets
        $occupied = $statusCounts['occupied'];
        $available = $statusCounts['available'];
        $faulty = $statusCounts['faulty'];

        $raised = $positionCounts['raised'];
        $lowered = $positionCounts['lowered'];

        return response()->json([
            'total' => $total,
            // back-compat top-level keys
            'occupied' => $occupied,
            'available' => $available,
            'raised' => $raised,
            'lowered' => $lowered,
            'faulty' => $faulty,
            // more explicit breakdowns
            'status_counts' => $statusCounts,
            'position_counts' => $positionCounts,
            'last_updated' => now()->toIso8601String(),
        ]);
    }
}
