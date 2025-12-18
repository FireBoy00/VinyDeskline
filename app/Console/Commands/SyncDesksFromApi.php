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
            // We don't need to fetch full desk data anymore - just ensure the desk exists
            // Real-time data (position, speed, status, etc.) should be fetched from API when needed
            
            $desk = Desk::where('desk_id', $deskId)->first();

            if ($desk) {
                // If desk was previously marked as removed, restore it
                if ($desk->is_removed_from_api) {
                    $desk->update(['is_removed_from_api' => false]);
                    $updatedCount++;
                }
            } else {
                // Create new desk with just the ID - room/floor assignments are managed separately
                Desk::create([
                    'desk_id' => $deskId,
                    'is_removed_from_api' => false,
                ]);
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

