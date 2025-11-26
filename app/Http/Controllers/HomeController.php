<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class HomeController extends Controller
{
    /**
     * Display the home page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Display the settings page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function settings()
    {
        return view('settings');
    }

    /**
     * Update user information (name, surname, email, height, age).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
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
            'message' => 'User information updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Reset user data (height and age).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetUserData()
    {
        $user = Auth::user();
        $user->update([
            'height' => null,
            'age' => null,
        ]);

        return response()->json([
            'message' => 'User data reset successfully',
            'user' => $user
        ]);
    }
}
