<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\HeightCalculationService;
use Illuminate\Console\Command;

class RecalculateUserHeights extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'heights:recalculate {--user-id= : Recalculate for a specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate optimal sitting and standing heights for users based on their height';

    protected $heightCalculationService;

    public function __construct(HeightCalculationService $heightCalculationService)
    {
        parent::__construct();
        $this->heightCalculationService = $heightCalculationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user-id');

        if ($userId) {
            // Recalculate for a specific user
            return $this->recalculateForUser($userId);
        }

        // Recalculate for all users with a height
        return $this->recalculateForAllUsers();
    }

    /**
     * Recalculate heights for a specific user
     */
    private function recalculateForUser(int $userId): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }

        if (!$user->height) {
            $this->warn("User {$user->full_name} has no height set. Skipping.");
            return 0;
        }

        $optimalHeights = $this->heightCalculationService->calculateOptimalHeights($user->height);

        if ($optimalHeights) {
            $user->update([
                'optimal_sitting_height' => $optimalHeights['sitting_height_mm'],
                'optimal_standing_height' => $optimalHeights['standing_height_mm'],
            ]);

            $this->info("✓ Recalculated heights for {$user->full_name} (ID: {$userId})");
            $this->line("  Height: {$user->height} cm");
            $this->line("  Sitting: {$optimalHeights['sitting_height_mm']} mm");
            $this->line("  Standing: {$optimalHeights['standing_height_mm']} mm");

            return 0;
        }

        $this->error("Failed to calculate heights for user {$user->full_name}.");
        return 1;
    }

    /**
     * Recalculate heights for all users with a height set
     */
    private function recalculateForAllUsers(): int
    {
        $users = User::whereNotNull('height')->get();

        if ($users->isEmpty()) {
            $this->warn('No users with height found.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $optimalHeights = $this->heightCalculationService->calculateOptimalHeights($user->height);

            if ($optimalHeights) {
                $user->update([
                    'optimal_sitting_height' => $optimalHeights['sitting_height_mm'],
                    'optimal_standing_height' => $optimalHeights['standing_height_mm'],
                ]);
                $updated++;
            } else {
                $skipped++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->info("✓ Recalculation complete!");
        $this->info("  Updated: {$updated} users");
        if ($skipped > 0) {
            $this->warn("  Skipped: {$skipped} users");
        }

        return 0;
    }
}
