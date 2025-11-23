<?php

namespace App\Http\Controllers;
use App\Models\Schedule;


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
        $uniformSchedules = Schedule::where('type', 'uniform')->get();
        $cleaningSchedules = Schedule::where('type', 'cleaning')->get();

        return view('schedules', compact('uniformSchedules','cleaningSchedules'));
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
