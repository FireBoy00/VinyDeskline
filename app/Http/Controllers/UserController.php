<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Get a specific user's data
     */
    public function show($id)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'height' => 'nullable|integer|min:0',
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
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'height' => 'nullable|integer|min:0',
            'age' => 'nullable|integer|min:0',
            'is_admin' => 'boolean',
            'needs_personalization' => 'boolean',
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
        // Check if user is admin
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

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
}
