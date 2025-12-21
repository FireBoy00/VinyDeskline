<?php

namespace App\Http\Controllers;

use App\Models\Desk;
use App\Models\User;
use App\Models\Floor;
use App\Services\DeskApiService;
use Illuminate\Http\Request;

class ArrangementController extends Controller
{
    protected $deskApiService;

    public function __construct(DeskApiService $deskApiService)
    {
        $this->deskApiService = $deskApiService;
    }

    /**
     * Display the desk arrangement page (initial load without desk data)
     */
    public function index()
    {
        // Get all users for assignment dropdown
        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        
        return view('arrangement', compact('users'));
    }

    /**
     * API endpoint to fetch enriched desk data
     */
    public function getDesks()
    {
        try {
            // Get all desks from database with their relationships
            $desks = Desk::with(['user', 'room.floor', 'floor'])
                ->where('is_removed_from_api', false)
                ->get();

            // Get all floors with their rooms
            $floors = Floor::with('rooms')->orderByDesc('floor_number')->get();

            // Enrich each desk with real-time API data
            $enrichedDesks = $desks->map(function ($desk) {
                $apiData = $this->deskApiService->getDeskData($desk->desk_id);
                
                // Merge database and API data
                return [
                    'desk_id' => $desk->desk_id,
                    'display_name' => $apiData['config']['name'] ?? $desk->name ?? $desk->desk_id,
                    'current_position' => $apiData['state']['position_mm'] ?? $desk->position_mm,
                    'current_status' => $apiData['state']['status'] ?? $desk->status ?? 'Normal',
                    'manufacturer' => $apiData['config']['manufacturer'] ?? $desk->manufacturer ?? 'N/A',
                    'activations' => $apiData['usage']['activationsCounter'] ?? $desk->activations_counter ?? 0,
                    'sit_stand' => $apiData['usage']['sitStandCounter'] ?? $desk->sit_stand_counter ?? 0,
                    'assigned_user_id' => $desk->user ? $desk->user->id : null,
                    'floor_id' => $desk->floor_id,
                    'floor_name' => $desk->floor ? $desk->floor->name : null,
                    'floor_number' => $desk->floor ? $desk->floor->floor_number : null,
                    'room_id' => $desk->room_id,
                ];
            });

            // Group desks by floor
            $desksByFloor = $enrichedDesks->groupBy(function ($desk) {
                if ($desk['floor_number'] !== null) {
                    return $desk['floor_number'];
                }
                return 'unassigned';
            });

            // Sort floors from highest to lowest number, with unassigned at the end
            $sortedFloors = $desksByFloor->sortKeysUsing(function ($a, $b) {
                if ($a === 'unassigned') return 1;
                if ($b === 'unassigned') return -1;
                return (int)$b <=> (int)$a; // Descending order
            });

            // Prepare rooms by floor
            $roomsByFloor = [];
            foreach ($floors as $floor) {
                $roomsByFloor[$floor->floor_number] = $floor->rooms->map(function ($room) use ($enrichedDesks) {
                    $desksInRoom = $enrichedDesks->where('room_id', $room->id)->values();
                    return [
                        'id' => $room->id,
                        'name' => $room->name,
                        'description' => $room->description,
                        'floor_id' => $room->floor_id,
                        'desks_count' => $desksInRoom->count(),
                    ];
                })->values();
            }

            return response()->json([
                'success' => true,
                'desks_by_floor' => $sortedFloors,
                'rooms_by_floor' => $roomsByFloor,
                'total_desks' => $enrichedDesks->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load desks: ' . $e->getMessage(),
            ], 500);
        }
    }
}
