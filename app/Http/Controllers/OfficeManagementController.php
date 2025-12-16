<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Desk;
use Illuminate\Support\Facades\Validator;

class OfficeManagementController extends Controller
{
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
        $floors = Floor::withCount(['rooms', 'desks'])->orderBy('floor_number')->get();
        
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
        
        // Check if floor has rooms or desks
        if ($floor->rooms()->count() > 0 || $floor->desks()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete floor with assigned rooms or desks'
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
     * Assign desk to floor and/or room
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

        $desk->update($request->only(['floor_id', 'room_id']));
        $desk->load(['floor', 'room']);

        return response()->json([
            'success' => true,
            'message' => 'Desk location updated successfully',
            'desk' => $desk
        ]);
    }
}

