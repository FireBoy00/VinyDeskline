<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'height' => 'required|integer',
            'start_time' => 'required',
            'end_time' => 'required',
            'frequency' => 'required|in:daily,once,multiple',
            'dates' => 'nullable|array',
            'dates.*' => 'date_format:Y-m-d',
        ]);

        $schedules =[];

        if ($data['frequency'] === 'daily') {
            $schedule = Schedule::create([
                'type' => $data['type'],
                'title' => $data['title'],
                'height' => $data['height'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'frequency' => 'daily',
            ]);
            $schedules[] = $schedule;

        } else {
            foreach ($data['dates'] ?? [] as $date) {
            $schedule = Schedule::create([
                'type' => $data['type'],
                'title' => $data['title'],
                'height' => $data['height'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'frequency' => $data['frequency'],
                'date' => $date,
            ]);
            $schedules[] = $schedule;
        }
        }

        return response()->json([
            'message' => 'Schedule saved',
            'id' => $schedule->id,
            'schedules' => $schedules
        ]);
    }
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json(['message' => 'Deleted']);
    }
}

