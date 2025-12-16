<?php

namespace App\Http\Controllers;
use App\Models\Schedule;
use Carbon\Carbon;

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
        $uniformSchedules = Schedule::where('type', 'uniform')->get();
        $cleaningSchedules = Schedule::where('type', 'cleaning')->get();

        return view('schedules', compact('uniformSchedules','cleaningSchedules'));
    }

    /**
     * Display the desk arrangement page (redirects to ArrangementController).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function arrangement()
    {
        return redirect()->route('arrangement');
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
    public function userManagement(Request $request)
    {
        $query = \App\Models\User::with('desk');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ['%' . strtolower($search) . '%'])
                  ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
            });
        }

        // User type filter
        if ($request->filled('user_type') && $request->user_type !== 'all') {
            if ($request->user_type === 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->user_type === 'regular') {
                $query->where('is_admin', false);
            }
        }

        // Personalization filter
        if ($request->filled('personalization') && $request->personalization !== 'all') {
            if ($request->personalization === 'completed') {
                $query->where('needs_personalization', false);
            } elseif ($request->personalization === 'needs') {
                $query->where('needs_personalization', true);
            }
        }

        // Age filter
        if ($request->filled('age_comparison') && $request->age_comparison !== 'any' && $request->filled('age_value')) {
            $ageValue = (int) $request->age_value;
            switch ($request->age_comparison) {
                case 'equal':
                    $query->where('age', $ageValue);
                    break;
                case 'above':
                    $query->where('age', '>', $ageValue);
                    break;
                case 'below':
                    $query->where('age', '<', $ageValue);
                    break;
            }
        }

        // Height filter
        if ($request->filled('height_comparison') && $request->height_comparison !== 'any' && $request->filled('height_value')) {
            $heightValue = (int) $request->height_value;
            switch ($request->height_comparison) {
                case 'equal':
                    $query->where('height', $heightValue);
                    break;
                case 'above':
                    $query->where('height', '>', $heightValue);
                    break;
                case 'below':
                    $query->where('height', '<', $heightValue);
                    break;
            }
        }

        $users = $query->paginate(10)->appends($request->except('page'));
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

    public function nextSchedules(){
        $now = Carbon::now();
        $getNext = function ($type) use ($now){
            $schedules = Schedule::where('type', $type) -> get();
            $nextSchedule = null;
            $nextDateTime = null;

            foreach($schedules as $schedule){
                if ($schedule->frequency ==='daily'){
                    $datetime = Carbon::today()->setTimeFromTimeString($schedule->start_time);
                    if ($datetime->lt($now)){
                        $datetime->addDay();
                    }
                } else {
                    if(!$schedule->date){
                        continue;
                    }
                    $datetime = Carbon::parse($schedule->date. ' ' . $schedule->start_time);

                    if ($datetime->lt($now)){
                        continue;
                    }
                    
                }
                if (!$nextDateTime || $datetime->lt($nextDateTime)) {
                    $nextDateTime = $datetime;
                    $schedule->next_datetime = $datetime->format('Y-m-d\TH:i');
                    $nextSchedule = $schedule;
                }
            }
            return $nextSchedule;
        };
        return response()->json([
            'next_uniform'  => $getNext('uniform'),
            'next_cleaning' => $getNext('cleaning'),
        ]);
    }
}
