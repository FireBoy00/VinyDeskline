<?php

namespace App\Observers;

use App\Models\User;
use App\Services\HeightCalculationService;

class UserHeightObserver
{
    protected $heightCalculationService;

    public function __construct(HeightCalculationService $heightCalculationService)
    {
        $this->heightCalculationService = $heightCalculationService;
    }

    /**
     * Handle the User "updating" event.
     * This fires before the model is saved.
     */
    public function updating(User $user): void
    {
        // Check if height attribute has changed
        if ($user->isDirty('height')) {
            $newHeight = $user->getAttribute('height');

            // Calculate optimal heights based on the new height
            $optimalHeights = $this->heightCalculationService->calculateOptimalHeights($newHeight);

            // Update the optimal heights
            if ($optimalHeights) {
                $user->optimal_sitting_height = $optimalHeights['sitting_height_mm'];
                $user->optimal_standing_height = $optimalHeights['standing_height_mm'];
            } else {
                // If height is null or invalid, clear the optimal heights
                $user->optimal_sitting_height = null;
                $user->optimal_standing_height = null;
            }
        }
    }

    /**
     * Handle the User "creating" event.
     * This fires before the model is initially saved (new records).
     */
    public function creating(User $user): void
    {
        // If height is provided on creation, calculate optimal heights
        if ($user->getAttribute('height')) {
            $optimalHeights = $this->heightCalculationService->calculateOptimalHeights($user->getAttribute('height'));

            if ($optimalHeights) {
                $user->optimal_sitting_height = $optimalHeights['sitting_height_mm'];
                $user->optimal_standing_height = $optimalHeights['standing_height_mm'];
            }
        }
    }
}
