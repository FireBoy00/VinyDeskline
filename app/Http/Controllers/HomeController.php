<?php

namespace App\Http\Controllers;


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

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Later add auth logic here
        return redirect()->route('personalize'); 
    }

    public function personalize()
    {
        return view('personalize');
    }

    public function savePersonalization(Request $request)
    {
        // Handle personalization data here (e.g., save to database)
        return redirect()->route('home'); 
    }
}
