<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeskApiService;
use App\Models\Desk;
use App\Models\DeskMetric;
use Illuminate\Support\Facades\Log;

class CollectDeskMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'desks:collect-metrics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect desk metrics (height, sitting/standing status) from all active desks';

    protected $deskApiService;

    public function __construct(DeskApiService $deskApiService)
    {
        parent::__construct();
        $this->deskApiService = $deskApiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Collecting desk metrics...');

        // Get all active desks (not removed from API)
        $desks = Desk::where('is_removed_from_api', false)->get();

        if ($desks->isEmpty()) {
            $this->warn('No active desks found in database');
            return 0;
        }

        $this->info("Found {$desks->count()} active desks");

        $successCount = 0;
        $errorCount = 0;

        foreach ($desks as $desk) {
            $deskData = $this->deskApiService->getDeskData($desk->desk_id);

            if ($deskData === null) {
                $this->warn("Failed to fetch data for desk: {$desk->desk_id}");
                $errorCount++;
                continue;
            }

            // Extract position from state
            $state = $deskData['state'] ?? [];
            $positionMm = $state['position_mm'] ?? null;

            if ($positionMm === null) {
                $this->warn("No position data for desk: {$desk->desk_id}");
                $errorCount++;
                continue;
            }

            // Determine if user is sitting (height < 1000mm) or standing (>= 1000mm)
            $isSitting = $positionMm < 1000;

            // Store metric
            DeskMetric::create([
                'desk_id' => $desk->desk_id,
                'height_mm' => $positionMm,
                'is_sitting' => $isSitting,
                'recorded_at' => now(),
            ]);

            // Also update the desk's current position
            $desk->update(['position_mm' => $positionMm]);

            $successCount++;
        }

        $this->info("Metrics collection complete!");
        $this->info("Success: {$successCount}, Errors: {$errorCount}");

        Log::info('Desk metrics collected', [
            'success' => $successCount,
            'errors' => $errorCount,
        ]);

        return 0;
    }
}

