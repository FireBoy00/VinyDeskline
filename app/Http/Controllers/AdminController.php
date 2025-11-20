<?php

namespace App\Http\Controllers;


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
        return view('account');
    }
}
