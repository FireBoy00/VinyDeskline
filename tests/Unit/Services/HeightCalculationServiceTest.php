<?php

namespace Tests\Unit\Services;

use App\Services\HeightCalculationService;

beforeEach(function () {
    $this->service = new HeightCalculationService();
});

test('it returns null for invalid height input', function () {
    expect($this->service->calculateOptimalHeights(null))->toBeNull();
    expect($this->service->calculateOptimalHeights(0))->toBeNull();
    expect($this->service->calculateOptimalHeights(-100))->toBeNull();
});

test('it calculates ergonomic heights correctly for average height', function () {
    // 180cm person
    $result = $this->service->calculateOptimalHeights(180);

    // Sitting: ~40% of 1800mm + 150mm offset roughly = 720 + 150 = 870mm
    // Standing: ~47% of 1800mm + 100mm offset roughly = 846 + 100 = 946mm
    // Specific logic check from service:
    // Sitting: (1800 * 0.39) + 150 = 702 + 150 = 852
    // Standing: (1800 * 0.47) + 100 = 846 + 100 = 946

    expect($result)->toBeArray()
        ->and($result['sitting_height_mm'])->toBe(852)
        ->and($result['standing_height_mm'])->toBe(946);
});

test('it constrains calculation to desk physical limits', function () {
    // Very short person (100cm) -> Unrealistic but good for min bound
    // Sitting calc: (1000 * 0.39) + 150 = 390 + 150 = 540 < 680 (MIN)
    // Standing calc: (1000 * 0.47) + 100 = 470 + 100 = 570 < 680 (MIN)

    $result = $this->service->calculateOptimalHeights(100);

    expect($result['sitting_height_mm'])->toBe(680)
        ->and($result['standing_height_mm'])->toBe(680 + 300); // Enforced standing > sitting logic (force 300mm gap? check logic)

    // Logic check:
    // if standing <= sitting: standing = min(MAX, sitting + 300)
    // Here both clamped to 680 initially. 680 <= 680 is true.
    // standing = min(1320, 680 + 300) = 980.
    // Wait, let's re-read the service logic carefully or rely on test to assert behavior.

    // Actually, looking at the code I viewed earlier:
    // $sittingHeight = max(MIN, ...);
    // $standingHeight = max(MIN, ...);
    // if (standing <= sitting) { standing = min(MAX, sitting + 300); }

    // So for 100cm:
    // Raw sit: 540 -> Clamped: 680
    // Raw stand: 570 -> Clamped: 680
    // 680 <= 680 -> True
    // New stand: 680 + 300 = 980

    expect($result['sitting_height_mm'])->toBe(680)
        ->and($result['standing_height_mm'])->toBe(980);
});

test('it constrains calculation to max desk limits', function () {
    // Very tall person (250cm)
    // Sit: (2500 * 0.39) + 150 = 975 + 150 = 1125 (OK < 1320)
    // Stand: (2500 * 0.47) + 100 = 1175 + 100 = 1275 (OK < 1320)

    // Extremely tall (300cm)
    // Sit: (3000 * 0.39) + 150 = 1170 + 150 = 1320 (AT MAX)
    // Stand: (3000 * 0.47) + 100 = 1410 + 100 = 1510 (OVER MAX) -> Clamp to 1320

    $result = $this->service->calculateOptimalHeights(300);

    expect($result['sitting_height_mm'])->toBe(1320)
        ->and($result['standing_height_mm'])->toBe(1320);
});
