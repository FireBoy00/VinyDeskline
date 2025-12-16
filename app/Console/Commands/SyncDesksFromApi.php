<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DeskApiService;
use App\Models\Desk;
use Illuminate\Support\Facades\Log;

class SyncDesksFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'desks:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize desks from the API to the local database';

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
        $this->info('Starting desk synchronization from API...');

        // Get all desk IDs from API
        $apiDeskIds = $this->deskApiService->getAllDeskIds();

        if ($apiDeskIds === null) {
            $this->error('Failed to fetch desk IDs from API');
            return 1;
        }

        $this->info('Found ' . count($apiDeskIds) . ' desks in API');

        // Get all existing desk IDs from database
        $dbDeskIds = Desk::pluck('desk_id')->toArray();

        // Mark desks that no longer exist in API
        $removedDeskIds = array_diff($dbDeskIds, $apiDeskIds);
        if (count($removedDeskIds) > 0) {
            Desk::whereIn('desk_id', $removedDeskIds)
                ->update(['is_removed_from_api' => true]);
            $this->warn('Marked ' . count($removedDeskIds) . ' desks as removed from API');
        }

        // Add or update desks from API
        $addedCount = 0;
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($apiDeskIds as $deskId) {
            $deskData = $this->deskApiService->getDeskData($deskId);

            if ($deskData === null) {
                $this->warn("Failed to fetch data for desk: {$deskId}");
                $errorCount++;
                continue;
            }

            // Extract data from API response
            $config = $deskData['config'] ?? [];
            $state = $deskData['state'] ?? [];
            $usage = $deskData['usage'] ?? [];

            // Find or create desk
            $desk = Desk::where('desk_id', $deskId)->first();

            $dataToUpdate = [
                'is_removed_from_api' => false,
                'name' => $config['name'] ?? null,
                'manufacturer' => $config['manufacturer'] ?? null,
                'position_mm' => $state['position_mm'] ?? null,
                'speed_mms' => $state['speed_mms'] ?? null,
                'status' => $state['status'] ?? null,
                'activations_counter' => $usage['activationsCounter'] ?? 0,
                'sit_stand_counter' => $usage['sitStandCounter'] ?? 0,
                'last_synced_at' => now(),
            ];

            if ($desk) {
                // Update existing desk (keep room_id and floor_id)
                $desk->update($dataToUpdate);
                $updatedCount++;
            } else {
                // Create new desk
                Desk::create(array_merge(['desk_id' => $deskId], $dataToUpdate));
                $addedCount++;
            }
        }

        $this->info("Synchronization complete!");
        $this->info("Added: {$addedCount}, Updated: {$updatedCount}, Errors: {$errorCount}");

        Log::info('Desk synchronization completed', [
            'added' => $addedCount,
            'updated' => $updatedCount,
            'errors' => $errorCount,
            'removed' => count($removedDeskIds)
        ]);

        return 0;
    }
}

