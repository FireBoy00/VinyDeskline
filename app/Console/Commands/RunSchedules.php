<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Schedule;
use App\Models\Desk;
use Carbon\Carbon;

class RunSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-schedules';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDate = $now->format('Y-m-d');
        $apiKey = env('DESKS_API_KEY');
        $base = env('API_BASE');
        $url = "{$base}/{$apiKey}/desks";

        $schedules = Schedule::where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->where(function ($query) use ($currentDate) {
                $query->where('frequency', 'daily')
                    ->orWhere(function ($q) use ($currentDate) {
                        $q->whereIn('frequency', ['once', 'multiple'])
                            ->where('date', $currentDate);
                    });
            })
            ->orderBy('start_time', 'desc') 
            ->get();
        
        if ($schedules->isEmpty()) {
            return Command::SUCCESS;
        }
        $activeSchedule = $schedules->first();
        $targetHeight = $activeSchedule->height;


        $response = Http::get($url);

        if ($response->failed()) {
            $this->error("Failed to fetch desks from simulator");
            return Command::FAILURE;
        }

        $deskIds = $response->json();       

        foreach ($deskIds as $deskId) {
            $putUrl = "{$base}/{$apiKey}/desks/{$deskId}/state";
            
            try {
                $response = Http::put($putUrl, [
                    'position_mm' => $targetHeight
                ]);
                
                if ($response->successful()) {
                    $this->line("Desk {$deskId}: Position updated to {$targetHeight}mm.");
                } else {
                    $this->error("Desk {$deskId}: Failed to update position. Status: {$response->status()}");
                }
            } catch (\Exception $e) {
                $this->error("Desk {$deskId}: Connection error: " . $e->getMessage());
            }
        }
        $this->info('Schedule application completed.');
        return Command::SUCCESS;

    }
}
