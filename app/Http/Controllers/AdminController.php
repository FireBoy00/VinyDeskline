<?php

namespace App\Http\Controllers;
use App\Models\Schedule;
use App\Models\DeskMetric;
use App\Models\Desk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        return redirect()->route('admin.arrangement');
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

        // Desk assignment filter
        if ($request->filled('desk_assignment') && $request->desk_assignment !== 'all') {
            if ($request->desk_assignment === 'assigned') {
                $query->whereNotNull('desk_id');
            } elseif ($request->desk_assignment === 'unassigned') {
                $query->whereNull('desk_id');
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
     * Update user information (first_name, last_name).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
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

    public function getDashboardMetrics(Request $request)
    {
        // 1. Sit/Stand Timeline
        $timelineData = [];
        $userIds = $request->input('user_ids');
        
        $query = DeskMetric::join('desks', 'desk_metrics.desk_id', '=', 'desks.desk_id')
            ->join('users', 'desks.desk_id', '=', 'users.desk_id')
            ->whereDate('desk_metrics.recorded_at', Carbon::today())
            ->select('desk_metrics.*', 'users.id as user_id', 'users.first_name', 'users.last_name')
            ->orderBy('desk_metrics.recorded_at');

        if ($userIds) {
            $userIdsArray = is_array($userIds) ? $userIds : explode(',', $userIds);
            $query->whereIn('users.id', $userIdsArray);
        } else {
             // Default to top 5 users if no selection
             $topUsers = User::whereNotNull('desk_id')->take(5)->pluck('id');
             $query->whereIn('users.id', $topUsers);
        }

        $metrics = $query->get();
        
        $groupedMetrics = $metrics->groupBy('user_id');
        
        foreach ($groupedMetrics as $userId => $userMetrics) {
            $user = $userMetrics->first();
            $timelineData[] = [
                'name' => $user->first_name . ' ' . $user->last_name,
                'x' => $userMetrics->pluck('recorded_at')->map(fn($d) => $d->format('Y-m-d H:i:s')),
                'y' => $userMetrics->pluck('height_mm'),
            ];
        }

        // 2. Standing Percentage (Current Snapshot)
        // Get latest metric for each desk
        $latestMetrics = DeskMetric::select('desk_id', 'is_sitting')
            ->whereIn('id', function($q) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('desk_metrics')
                  ->groupBy('desk_id');
            })
            ->get();
            
        $totalReported = $latestMetrics->count();
        $standingCount = $latestMetrics->where('is_sitting', false)->count();
        $sittingCount = $latestMetrics->where('is_sitting', true)->count();
        
        $standingPercentage = [
            'standing' => $standingCount,
            'sitting' => $sittingCount
        ];

        // 3. Desk State Overview
        $totalDesks = Desk::count();
        $assignedDesks = User::whereNotNull('desk_id')->count();
        $availableDesks = $totalDesks - $assignedDesks;
        
        // For raised/lowered, we look at assigned desks and their latest status
        $assignedDeskIds = User::whereNotNull('desk_id')->pluck('desk_id')->toArray();
        
        $raisedCount = $latestMetrics->whereIn('desk_id', $assignedDeskIds)->where('is_sitting', false)->count();
        $loweredCount = $latestMetrics->whereIn('desk_id', $assignedDeskIds)->where('is_sitting', true)->count();
        
        $unavailableCount = Desk::where('is_removed_from_api', true)->count();
        
        $assignedWithMetricsCount = $raisedCount + $loweredCount;
        $occupiedNoMetrics = max(0, $assignedDesks - $assignedWithMetricsCount);
        
        $deskState = [
            'Available' => $availableDesks,
            'Occupied' => $occupiedNoMetrics,
            'Raised' => $raisedCount,
            'Lowered' => $loweredCount,
            'Unavailable' => $unavailableCount
        ];

        // 4. Daily Usage Duration (Last 5 days)
        $dailyUsage = [
            'days' => [],
            'usageData' => [
                'Sitting' => [],
                'Standing' => [],
                'Cleaning' => [],
                'Uniform' => []
            ]
        ];
        
        for ($i = 4; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dailyUsage['days'][] = $date->format('D');
            
            // Count metrics for this day
            $dayMetrics = DeskMetric::whereDate('recorded_at', $date)->get();
            $sitCount = $dayMetrics->where('is_sitting', true)->count();
            $standCount = $dayMetrics->where('is_sitting', false)->count();
            
            // Approximation: 1 sample = 15 minutes = 0.25 hours
            $dailyUsage['usageData']['Sitting'][] = $sitCount * 0.25;
            $dailyUsage['usageData']['Standing'][] = $standCount * 0.25;
            $dailyUsage['usageData']['Cleaning'][] = 0; // Placeholder
            $dailyUsage['usageData']['Uniform'][] = 0; // Placeholder
        }

        // 5. Environmental Data (Last 24 hours)
        $sensorMetrics = \App\Models\SensorMetric::where('recorded_at', '>=', now()->subDay())
            ->orderBy('recorded_at', 'asc')
            ->get();

        $environmentalData = null;
        if ($sensorMetrics->isNotEmpty()) {
            $environmentalData = [
                'temperature' => [
                    'x' => $sensorMetrics->pluck('recorded_at')->map(fn($d) => $d->format('Y-m-d H:i:s')),
                    'y' => $sensorMetrics->pluck('temperature'),
                ],
                'light' => [
                    'x' => $sensorMetrics->pluck('recorded_at')->map(fn($d) => $d->format('Y-m-d H:i:s')),
                    'y' => $sensorMetrics->pluck('light'),
                ],
                'humidity' => [
                    'x' => $sensorMetrics->pluck('recorded_at')->map(fn($d) => $d->format('Y-m-d H:i:s')),
                    'y' => $sensorMetrics->pluck('humidity'),
                ],
            ];
        }

        return response()->json([
            'timeline' => $timelineData,
            'standingPercentage' => $standingPercentage,
            'deskState' => $deskState,
            'dailyUsage' => $dailyUsage,
            'environmentalData' => $environmentalData,
            'total_users' => User::count(),
            'total_desks' => $totalDesks,
            'assigned' => $assignedDesks,
            'sitting' => $sittingCount,
            'standing' => $standingCount,
            'active' => $assignedWithMetricsCount,
            'all_users' => User::whereNotNull('desk_id')->select('id', 'first_name', 'last_name')->get()
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
