<?php

namespace App\Services;

class HeightCalculationService
{
    // Desk constraints in millimeters
    private const MIN_DESK_HEIGHT_MM = 680;
    private const MAX_DESK_HEIGHT_MM = 1320;

    /**
     * Calculate optimal sitting and standing heights based on user height
     *
     * Uses ergonomic standards:
     * - Sitting: Elbows should be at 90 degrees when hands are on keyboard
     * - Standing: Elbows should be at approximately 90 degrees, feet on floor
     *
     * @param int|null $heightCm User height in centimeters
     * @return array|null Array with 'sitting_height_mm' and 'standing_height_mm', or null if height is invalid
     */
    public function calculateOptimalHeights(?int $heightCm): ?array
    {
        // Handle null height
        if ($heightCm === null || $heightCm <= 0) {
            return null;
        }

        // Convert height from cm to mm for calculations
        $heightMm = $heightCm * 10;

        // Ergonomic sitting position: approximately 40% of user height plus seat height offset
        // Average office chair height is around 400-450mm, desk surface at elbows when sitting
        $sittingHeight = (int)($heightMm * 0.39) + 150;

        // Ergonomic standing position: approximately 47% of user height (elbows at desk height)
        $standingHeight = (int)($heightMm * 0.47) + 100;

        // Constrain to desk physical limits
        $sittingHeight = max(self::MIN_DESK_HEIGHT_MM, min(self::MAX_DESK_HEIGHT_MM, $sittingHeight));
        $standingHeight = max(self::MIN_DESK_HEIGHT_MM, min(self::MAX_DESK_HEIGHT_MM, $standingHeight));

        // Ensure standing is higher than sitting
        if ($standingHeight <= $sittingHeight) {
            $standingHeight = min(self::MAX_DESK_HEIGHT_MM, $sittingHeight + 300);
        }

        return [
            'sitting_height_mm' => $sittingHeight,
            'standing_height_mm' => $standingHeight,
        ];
    }
}
