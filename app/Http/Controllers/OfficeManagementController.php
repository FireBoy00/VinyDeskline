<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Desk;
use App\Services\DeskApiService;
use Illuminate\Support\Facades\Validator;

class OfficeManagementController extends Controller
{
    protected $deskApiService;

    public function __construct(DeskApiService $deskApiService)
    {
        $this->deskApiService = $deskApiService;
    }
    /**
     * Show the office management page
     */
    public function index()
    {
        $floors = Floor::with('rooms')->orderBy('floor_number')->get();
        $rooms = Room::with('floor')->get();
        
        return view('office-management', compact('floors', 'rooms'));
    }

    // ===== Floor Management =====

    /**
     * Get all floors
     */
    public function getFloors()
    {
        $floors = Floor::with('rooms')->orderBy('floor_number')->get();
        
        // Count ALL desks on this floor (direct + in rooms on this floor)
        $floors->each(function ($floor) {
            $floor->desks_count = Desk::where('floor_id', $floor->id)
                ->where('is_removed_from_api', false)
                ->count();
            $floor->rooms_count = $floor->rooms->count();
        });
        
        return response()->json([
            'success' => true,
            'floors' => $floors
        ]);
    }

    /**
     * Create a new floor
     */
    public function createFloor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|unique:floors,floor_number',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $floor = Floor::create($request->only(['name', 'floor_number', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Floor created successfully',
            'floor' => $floor
        ]);
    }

    /**
     * Update a floor
     */
    public function updateFloor(Request $request, $id)
    {
        $floor = Floor::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor_number' => 'required|integer|unique:floors,floor_number,' . $id,
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $floor->update($request->only(['name', 'floor_number', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Floor updated successfully',
            'floor' => $floor
        ]);
    }

    /**
     * Delete a floor
     */
    public function deleteFloor($id)
    {
        $floor = Floor::findOrFail($id);
        
        // Check if floor has rooms (which may have desks)
        if ($floor->rooms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete floor with assigned rooms'
            ], 422);
        }

        $floor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Floor deleted successfully'
        ]);
    }

    // ===== Room Management =====

    /**
     * Get all rooms
     */
    public function getRooms()
    {
        $rooms = Room::with(['floor'])->withCount('desks')->get();
        
        return response()->json([
            'success' => true,
            'rooms' => $rooms
        ]);
    }

    /**
     * Create a new room
     */
    public function createRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor_id' => 'nullable|exists:floors,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $room = Room::create($request->only(['name', 'floor_id', 'description']));
        $room->load('floor');

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully',
            'room' => $room
        ]);
    }

    /**
     * Update a room
     */
    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'floor_id' => 'nullable|exists:floors,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // If floor is changing, update all desks in this room to the new floor
        if ($request->has('floor_id') && $room->floor_id !== $request->floor_id) {
            Desk::where('room_id', $room->id)
                ->update(['floor_id' => $request->floor_id]);
        }

        $room->update($request->only(['name', 'floor_id', 'description']));
        $room->load('floor');

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully',
            'room' => $room
        ]);
    }

    /**
     * Delete a room
     */
    public function deleteRoom($id)
    {
        $room = Room::findOrFail($id);
        
        // Check if room has desks
        if ($room->desks()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete room with assigned desks'
            ], 422);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully'
        ]);
    }

    // ===== Desk Location Assignment =====

    /**
     * Get all desks with real-time API data
     */
    public function getDesks()
    {
        $desks = Desk::with(['room.floor', 'user'])
            ->where('is_removed_from_api', false)
            ->get();
        
        // Enrich with real-time API data
        $desksWithApiData = $desks->map(function ($desk) {
            $apiData = $this->deskApiService->getDeskData($desk->desk_id);
            
            // Get floor from room if desk is in a room
            $floor = $desk->room ? $desk->room->floor : null;
            
            // Use stored name, fallback to API if not stored
            $deskName = $desk->name ?? ($apiData['config']['name'] ?? 'Unknown Desk');
            
            return [
                'desk_id' => $desk->desk_id,
                'room_id' => $desk->room_id,
                'floor_id' => $desk->floor_id, // Computed attribute from room
                'is_removed_from_api' => $desk->is_removed_from_api,
                'room' => $desk->room,
                'floor' => $floor,
                'user' => $desk->user,
                // Real-time data from API
                'name' => $deskName,
                'manufacturer' => $apiData['config']['manufacturer'] ?? null,
                'position_mm' => $apiData['state']['position_mm'] ?? null,
                'speed_mms' => $apiData['state']['speed_mms'] ?? null,
                'status' => $apiData['state']['status'] ?? null,
                'activations_counter' => $apiData['usage']['activationsCounter'] ?? 0,
                'sit_stand_counter' => $apiData['usage']['sitStandCounter'] ?? 0,
            ];
        });
        
        return response()->json([
            'success' => true,
            'desks' => $desksWithApiData
        ]);
    }

    /**
     * Assign desk to floor and/or room
     * When assigning to room: desk takes room's floor_id
     * When assigning to floor directly: room_id must be null
     */
    public function assignDeskLocation(Request $request, $deskId)
    {
        $desk = Desk::where('desk_id', $deskId)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'floor_id' => 'nullable|exists:floors,id',
            'room_id' => 'nullable|exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Logic: If assigning to a room, desk takes the room's floor
        if ($request->room_id) {
            $room = Room::findOrFail($request->room_id);
            $desk->update([
                'room_id' => $request->room_id,
                'floor_id' => $room->floor_id, // Desk gets room's floor
            ]);
        } else {
            // Assigning directly to floor or unassigning
            $desk->update([
                'room_id' => null,
                'floor_id' => $request->floor_id,
            ]);
        }

        $desk->load(['floor', 'room.floor']);

        return response()->json([
            'success' => true,
            'message' => 'Desk location updated successfully',
            'desk' => $desk
        ]);
    }
}

