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
        $user = Auth::user();
        return view('home', ['user' => $user]);
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
     * Display the about page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function about()
    {
        return view('about');
    }

    /**
     * Display the help page.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function help()
    {
        return view('help');
    }

    /**
     * Update user information (name, surname, height, age).
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
     * Update user settings (height and age).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'height' => ['nullable', 'numeric', 'min:100', 'max:250'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
            'optimal_sitting_height' => ['nullable', 'integer', 'min:680', 'max:1320'],
            'optimal_standing_height' => ['nullable', 'integer', 'min:680', 'max:1320'],
            'custom_height_1' => ['nullable', 'integer', 'min:680', 'max:1320'],
            'custom_height_2' => ['nullable', 'integer', 'min:680', 'max:1320'],    
            'desk_id' => ['nullable', 'string', 'max:255'],
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
     * Reset user data (height and age).
     *
     * This route is protected by Laravel's 'web' middleware, which enforces CSRF validation.
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

    /**
     * Get desk metrics for the authenticated user's desk.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDeskMetrics()
    {
        $user = Auth::user();
        
        if (!$user->desk_id) {
            return response()->json([
                'error' => 'No desk assigned to user',
                'metrics' => []
            ], 400);
        }

        // Fetch all metrics for user's desk from the last 30 days
        $metrics = \App\Models\DeskMetric::where('desk_id', $user->desk_id)
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at', 'asc')
            ->get();

        if ($metrics->isEmpty()) {
            return response()->json([
                'error' => 'No metrics found for this desk',
                'metrics' => []
            ], 404);
        }

        // Transform metrics into a useful format
        $transformedMetrics = $metrics->map(function ($metric) {
            return [
                'height_mm' => $metric->height_mm,
                'is_sitting' => $metric->is_sitting,
                'recorded_at' => $metric->recorded_at->toIso8601String(),
                'timestamp' => $metric->recorded_at->timestamp
            ];
        });

        return response()->json([
            'success' => true,
            'metrics' => $transformedMetrics,
            'count' => $transformedMetrics->count(),
            'desk_id' => $user->desk_id
        ]);
    }

    /**
     * Update user's custom desk position.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function updateCustom(Request $request)
    {
        $user = Auth::user();

        $index = $request->index;
        $nameField = "custom_name_$index";
        $heightField = "custom_height_$index";

        if ($request->position_mm == '' || $request->position_mm == null) 
        {
            return response()->json(['success' => false, 'message' => 'Height cannot be empty'], 400);
            
        }
        else if($request->position_mm > 1320)
        {
            return response()->json(['success' => false, 'message' => 'Height over 132cm'], 400);
        }
        else if($request->position_mm < 680)
        {
            return response()->json(['success' => false, 'message' => 'Given height is under 68cm'], 400);
        }


        $request->validate([
            'index' => 'required|in:1,2',
            'name' => 'nullable|string|max:255',
            'position_mm' => 'nullable|numeric|min:680|max:1320',
        ]);

        $user->$nameField = $request->name;
        $user->$heightField = $request->position_mm;
        $user->save();

        
        return response()->json(['success' => true,
        'height' => $user->$heightField]);
    }

    
}
