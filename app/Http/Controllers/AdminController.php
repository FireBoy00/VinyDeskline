<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Display the schedules page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function schedules()
    {
        return view('schedules');
    }

    /**
     * Display the desk arrangement page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function arrangement()
    {
        return view('arrangement');
    }

    /**
     * Display the account settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function account()
    {
        return view('admin-account');
    }

    /**
     * Display the user management page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function userManagement()
    {
        $users = \App\Models\User::all();
        $currentUserId = Auth::id();
        return view('user-management', compact('users', 'currentUserId'));
    }

    /**
     * Update user information (first_name, last_name, email).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->update($validator->validated());

        return response()->json([
            'message' => 'User information updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Update user settings (height, age).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $user->update($validator->validated());

        return response()->json([
            'message' => 'User settings updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Reset user settings (height and age).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetUserSettings()
    {
        $user = Auth::user();
        $user->update([
            'height' => null,
            'age' => null,
        ]);

        return response()->json([
            'message' => 'User settings reset successfully',
            'user' => $user
        ]);
    }
}
