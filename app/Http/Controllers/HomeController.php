<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function admin()
    {
        return view('dashboard');
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        // Later add auth logic here
        return redirect()->route('home'); 
    }
}
