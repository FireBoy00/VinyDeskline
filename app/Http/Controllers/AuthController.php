<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Display the login page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('login');
    }

    /**
     * Handle login request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            
            // Check if user needs to complete personalization
            if ($user->needs_personalization) {
                return redirect()->route('personalize');
            }

            return redirect()->intended('home');
        }

        throw ValidationException::withMessages([
            'email' => __('The provided credentials do not match our records.'),
        ]);
    }

    /**
     * Display the personalization page.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPersonalize()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // If user already personalized, redirect to home
        if (!$user->needs_personalization) {
            return redirect()->route('home');
        }

        return view('personalize');
    }

    /**
     * Handle personalization data submission.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function savePersonalization(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
        ]);

        $user = Auth::user();
        
        $user->update([
            'height' => $validated['height'] ?? null,
            'age' => $validated['age'] ?? null,
            'needs_personalization' => false,
        ]);

        return redirect()->route('home')->with('success', 'Your profile has been personalized!');
    }

    /**
     * Skip personalization.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function skipPersonalization()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        $user->update([
            'needs_personalization' => false,
        ]);

        return redirect()->route('home');
    }

    /**
     * Handle logout request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}
