<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Desk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Note: Authorization is handled at the route level via middleware.
     * All routes in this controller are protected by 'auth' and 'admin' middleware
     * as defined in routes/web.php, eliminating the need for duplicate checks.
     */

    /**
     * Get a specific user's data
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'height' => $user->height,
            'age' => $user->age,
            'is_admin' => $user->is_admin,
            'needs_personalization' => $user->needs_personalization,
            'desk_id' => $user->desk_id,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'height' => 'nullable|numeric|min:0',
            'age' => 'nullable|integer|min:0',
            'is_admin' => 'boolean',
            'needs_personalization' => 'boolean',
        ]);

        // Prepare user data
        $needsPersonalization = $validated['needs_personalization'] ?? false;
        
        // Create user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'height' => $needsPersonalization ? null : ($validated['height'] ?? null),
            'age' => $needsPersonalization ? null : ($validated['age'] ?? null),
            'is_admin' => $validated['is_admin'] ?? false,
            'needs_personalization' => $needsPersonalization,
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Update a user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validate the request (ignore _method field used for Laravel method spoofing)
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'height' => 'nullable|numeric|min:0',
            'age' => 'nullable|integer|min:0',
            'is_admin' => 'boolean',
            'needs_personalization' => 'boolean',
            '_method' => 'sometimes|string', // Allow _method field for method spoofing
        ]);

        // Update user data
        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->is_admin = $validated['is_admin'] ?? false;
        $user->needs_personalization = $validated['needs_personalization'] ?? false;
        
        // If needs_personalization is true, clear height and age
        if ($user->needs_personalization) {
            $user->height = null;
            $user->age = null;
        } else {
            $user->height = $validated['height'] ?? null;
            $user->age = $validated['age'] ?? null;
        }
        
        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'You cannot delete your own account'], 400);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Assign desk to user
     */
    public function assignDesk(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'desk_id' => 'required|string|exists:desks,desk_id'
        ]);

        $deskId = $validated['desk_id'];

        // Check if another user is already assigned to this desk
        $existingUser = User::where('desk_id', $deskId)
            ->where('id', '!=', $userId)
            ->first();

        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'This desk is already assigned to another user'
            ], 422);
        }

        // Assign desk to user
        $user->update(['desk_id' => $deskId]);

        return response()->json([
            'success' => true,
            'message' => 'Desk assigned successfully',
            'user' => $user->load('desk')
        ]);
    }

    /**
     * Unassign desk from user
     */
    public function unassignDesk($userId)
    {
        $user = User::findOrFail($userId);

        if (!$user->desk_id) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a desk assigned'
            ], 404);
        }

        $user->update(['desk_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Desk unassigned successfully'
        ]);
    }
}

