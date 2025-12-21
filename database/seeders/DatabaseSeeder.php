<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Desk;
use App\Models\DeskMetric;
use App\Services\DeskApiService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    protected $deskApiService;

    public function __construct(DeskApiService $deskApiService)
    {
        $this->deskApiService = $deskApiService;
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ========== FLOORS ==========
        // Create a multi-floor office building structure
        $floors = [];
        $floors[] = Floor::factory()->floor(0)->create([
            'name' => 'Ground Floor',
            'description' => 'Main entrance, reception, and open office space',
        ]);
        $floors[] = Floor::factory()->floor(1)->create([
            'name' => 'First Floor',
            'description' => 'Executive offices and management teams',
        ]);
        $floors[] = Floor::factory()->floor(2)->create([
            'name' => 'Second Floor',
            'description' => 'Development and engineering teams',
        ]);
        $floors[] = Floor::factory()->floor(3)->create([
            'name' => 'Third Floor',
            'description' => 'Design, marketing, and creative departments',
        ]);

        // ========== ROOMS ==========
        // Ground Floor Rooms
        $groundFloorRooms = [
            Room::factory()->onFloor($floors[0])->create([
                'name' => 'Open Office A',
                'description' => 'Main open workspace with 15 desks',
            ]),
            Room::factory()->onFloor($floors[0])->create([
                'name' => 'Reception & Waiting',
                'description' => 'Client-facing reception area',
            ]),
            Room::factory()->onFloor($floors[0])->create([
                'name' => 'Meeting Room - Boardroom',
                'description' => 'Large conference room with AV equipment',
            ]),
        ];

        // First Floor Rooms
        $firstFloorRooms = [
            Room::factory()->onFloor($floors[1])->create([
                'name' => 'Executive Suite',
                'description' => 'Senior management offices',
            ]),
            Room::factory()->onFloor($floors[1])->create([
                'name' => 'Executive Meeting Room',
                'description' => 'Premium boardroom for important meetings',
            ]),
            Room::factory()->onFloor($floors[1])->create([
                'name' => 'HR Office',
                'description' => 'Human resources department',
            ]),
        ];

        // Second Floor Rooms
        $secondFloorRooms = [
            Room::factory()->onFloor($floors[2])->create([
                'name' => 'Backend Development Team',
                'description' => 'Server and database engineers workspace',
            ]),
            Room::factory()->onFloor($floors[2])->create([
                'name' => 'Frontend Development Team',
                'description' => 'Web and mobile developers workspace',
            ]),
            Room::factory()->onFloor($floors[2])->create([
                'name' => 'QA Testing Lab',
                'description' => 'Quality assurance and testing team',
            ]),
            Room::factory()->onFloor($floors[2])->create([
                'name' => 'DevOps & Infrastructure',
                'description' => 'Infrastructure and operations team',
            ]),
        ];

        // Third Floor Rooms
        $thirdFloorRooms = [
            Room::factory()->onFloor($floors[3])->create([
                'name' => 'Design Studio',
                'description' => 'UX/UI design team with creative space',
            ]),
            Room::factory()->onFloor($floors[3])->create([
                'name' => 'Marketing Department',
                'description' => 'Marketing team and communications hub',
            ]),
            Room::factory()->onFloor($floors[3])->create([
                'name' => 'Sales Team',
                'description' => 'Sales representatives workspace',
            ]),
        ];

        // ========== SYNC DESKS FROM API ==========
        // Attempt to sync desks from external API first
        $availableDeskIds = $this->syncDesksFromApi($secondFloorRooms);

        // Create test desks if API is unavailable and no desks exist
        if (empty($availableDeskIds)) {
            $availableDeskIds = $this->createTestDesks($secondFloorRooms);
        }

        // ========== SCHEDULES ==========
        // Create daily uniform schedules (sitting/standing rotation)
        Schedule::factory()->uniformSchedule()->daily()->create([
            'title' => 'Morning Sitting Session',
            'height' => 750,
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
        ]);

        Schedule::factory()->uniformSchedule()->daily()->create([
            'title' => 'Late Morning Standing',
            'height' => 1100,
            'start_time' => '10:30:00',
            'end_time' => '12:00:00',
        ]);

        Schedule::factory()->uniformSchedule()->daily()->create([
            'title' => 'Lunch Break Sitting',
            'height' => 750,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        Schedule::factory()->uniformSchedule()->daily()->create([
            'title' => 'Afternoon Standing Work',
            'height' => 1100,
            'start_time' => '13:00:00',
            'end_time' => '16:00:00',
        ]);

        Schedule::factory()->uniformSchedule()->daily()->create([
            'title' => 'End of Day Sitting',
            'height' => 750,
            'start_time' => '16:00:00',
            'end_time' => '17:30:00',
        ]);

        // Create cleaning schedules
        Schedule::factory()->cleaningSchedule()->daily()->create([
            'title' => 'Evening Desk Sanitization',
            'height' => 950,  // Neutral position for cleaning
            'start_time' => '17:30:00',
            'end_time' => '19:00:00',
        ]);

        Schedule::factory()->cleaningSchedule()->create([
            'title' => 'Weekly Deep Clean',
            'height' => 950,
            'start_time' => '06:00:00',
            'end_time' => '08:00:00',
            'date' => now()->addWeeks(1)->format('Y-m-d'),
            'frequency' => 'once',
        ]);

        // ========== USERS - COMPREHENSIVE VARIETY ==========
        // Assign desks to users (cycle through available desks)
        $deskIndex = 0;

        // === ADMIN USERS ===
        // Admin 1: CEO - Tall, personalized with custom heights
        $admin1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->create([
            'first_name' => 'Sarah',
            'last_name' => 'Mitchell',
            'email' => 'smit@vinydeskline.com',
            'height' => 185,
            'age' => 58,
            'is_admin' => true,
            'needs_personalization' => false,
            'optimal_sitting_height' => 820,
            'optimal_standing_height' => 1150,
            'custom_name_1' => 'Executive Meeting',
            'custom_height_1' => 900,
            'custom_name_2' => 'Video Call Position',
            'custom_height_2' => 850,
            'desk_id' => $admin1DeskId,
        ]);
        $this->createDeskMetrics($admin1DeskId, 50);

        // Admin 2: Operations Manager - Average height
        $admin2DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->admin()->average()->personalized()->create([
            'first_name' => 'Michael',
            'last_name' => 'Chen',
            'email' => 'mchen@vinydeskline.com',
            'desk_id' => $admin2DeskId,
        ]);
        $this->createDeskMetrics($admin2DeskId, 40);

        // Admin 3: Tech Lead - Short
        $admin3DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->admin()->short()->withCustomHeights()->create([
            'first_name' => 'Alex',
            'last_name' => 'Rodriguez',
            'email' => 'arod@vinydeskline.com',
            'desk_id' => $admin3DeskId,
        ]);
        $this->createDeskMetrics($admin3DeskId, 35);

        // === REGULAR USERS - DEVELOPMENT TEAM ===
        // Backend developer - Tall, prefers standing
        $dev1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->tall()->personalized()->create([
            'first_name' => 'James',
            'last_name' => 'Peterson',
            'email' => 'jpet@vinydeskline.com',
            'age' => 31,
            'desk_id' => $dev1DeskId,
        ]);
        $this->createDeskMetrics($dev1DeskId, 60, true); // More standing

        // Frontend developer - Average height, young
        $dev2DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->young()->average()->withCustomHeights()->create([
            'first_name' => 'Emma',
            'last_name' => 'Thompson',
            'email' => 'ethom@vinydeskline.com',
            'desk_id' => $dev2DeskId,
        ]);
        $this->createDeskMetrics($dev2DeskId, 45);

        // Backend developer - Senior
        $dev3DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->senior()->personalized()->create([
            'first_name' => 'Robert',
            'last_name' => 'Williams',
            'email' => 'rwil@vinydeskline.com',
            'desk_id' => $dev3DeskId,
        ]);
        $this->createDeskMetrics($dev3DeskId, 55);

        // === REGULAR USERS - DESIGN TEAM ===
        // Designer - Tall with custom presets
        $design1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->tall()->withCustomHeights()->create([
            'first_name' => 'Lisa',
            'last_name' => 'Novak',
            'email' => 'lnov@vinydeskline.com',
            'age' => 28,
            'desk_id' => $design1DeskId,
        ]);
        $this->createDeskMetrics($design1DeskId, 48);

        // UI Designer - Short, personalized
        $design2DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->short()->personalized()->create([
            'first_name' => 'Daniel',
            'last_name' => 'Park',
            'email' => 'dpar@vinydeskline.com',
            'age' => 27,
            'desk_id' => $design2DeskId,
        ]);
        $this->createDeskMetrics($design2DeskId, 42);

        // === REGULAR USERS - MARKETING & SALES ===
        // Marketing Manager
        $marketing1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->average()->personalized()->create([
            'first_name' => 'Jessica',
            'last_name' => 'Adams',
            'email' => 'jada@vinydeskline.com',
            'age' => 35,
            'desk_id' => $marketing1DeskId,
        ]);
        $this->createDeskMetrics($marketing1DeskId, 38);

        // Sales Representative - Young and tall
        $sales1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->tall()->young()->withCustomHeights()->create([
            'first_name' => 'Kevin',
            'last_name' => 'Martinez',
            'email' => 'kmar@vinydeskline.com',
            'desk_id' => $sales1DeskId,
        ]);
        $this->createDeskMetrics($sales1DeskId, 52, true); // More standing for calls

        // === USERS NEEDING PERSONALIZATION (No desks assigned yet) ===
        User::factory()->needsPersonalization()->create([
            'first_name' => 'Christopher',
            'last_name' => 'Johnson',
            'email' => 'cjoh@vinydeskline.com',
        ]);

        User::factory()->needsPersonalization()->create([
            'first_name' => 'Amanda',
            'last_name' => 'White',
            'email' => 'awhi@vinydeskline.com',
        ]);

        User::factory()->needsPersonalization()->unverified()->create([
            'first_name' => 'Thomas',
            'last_name' => 'Brown',
            'email' => 'tbro@vinydeskline.com',
        ]);

        User::factory()->needsPersonalization()->create([
            'first_name' => 'Victoria',
            'last_name' => 'Garcia',
            'email' => 'vgar@vinydeskline.com',
        ]);

        // === ADDITIONAL DIVERSE USERS ===
        // Very tall developer
        $dev4DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->create([
            'first_name' => 'Oliver',
            'last_name' => 'Anderson',
            'email' => 'ande@vinydeskline.com',
            'height' => 210,
            'age' => 29,
            'is_admin' => false,
            'needs_personalization' => false,
            'optimal_sitting_height' => 850,
            'optimal_standing_height' => 1180,
            'desk_id' => $dev4DeskId,
        ]);
        $this->createDeskMetrics($dev4DeskId, 65, true);

        // Very short employee
        $emp1DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->create([
            'first_name' => 'Sophie',
            'last_name' => 'Fisher',
            'email' => 'sfis@vinydeskline.com',
            'height' => 155,
            'age' => 24,
            'is_admin' => false,
            'needs_personalization' => false,
            'optimal_sitting_height' => 700,
            'optimal_standing_height' => 1000,
            'custom_name_1' => 'Comfortable Sitting',
            'custom_height_1' => 720,
            'desk_id' => $emp1DeskId,
        ]);
        $this->createDeskMetrics($emp1DeskId, 36);

        // Mid-age professional
        $emp2DeskId = $availableDeskIds[$deskIndex++ % count($availableDeskIds)];
        User::factory()->create([
            'first_name' => 'William',
            'last_name' => 'Taylor',
            'email' => 'wtay@vinydeskline.com',
            'height' => 178,
            'age' => 45,
            'is_admin' => false,
            'needs_personalization' => false,
            'optimal_sitting_height' => 780,
            'optimal_standing_height' => 1110,
            'custom_name_1' => 'Back Support',
            'custom_height_1' => 800,
            'custom_name_2' => 'Presentation',
            'custom_height_2' => 1050,
            'desk_id' => $emp2DeskId,
        ]);
        $this->createDeskMetrics($emp2DeskId, 50);

        // ========== SENSOR METRICS ==========
        $this->call(SensorMetricSeeder::class);
    }

    /**
     * Sync desks from API and return available desk IDs
     * 
     * @param array $rooms Rooms to assign desks to
     * @return array Array of desk IDs
     */
    private function syncDesksFromApi(array $rooms = []): array
    {
        try {
            // Get all desk IDs from API
            $apiDeskIds = $this->deskApiService->getAllDeskIds();

            if ($apiDeskIds === null || empty($apiDeskIds)) {
                echo "⚠️  Could not fetch desks from API. Using test desks instead.\n";
                return [];
            }

            echo "✓ Found " . count($apiDeskIds) . " desks from API\n";

            // Mark desks no longer in API
            $dbDeskIds = Desk::pluck('desk_id')->toArray();
            $removedDeskIds = array_diff($dbDeskIds, $apiDeskIds);
            if (count($removedDeskIds) > 0) {
                Desk::whereIn('desk_id', $removedDeskIds)
                    ->update(['is_removed_from_api' => true]);
            }

            // Distribution strategy:
            // Ground Floor: 5 desks (2 in Open Office, 2 directly on floor for reception, 1 in meeting room)
            // First Floor: 4 desks (2 directly on floor for lobby, 2 in executive suite)
            // Second Floor: 8 desks (spread across 4 development rooms)
            // Third Floor: 8 desks (spread across 3 creative rooms)
            
            $distributions = [
                // Ground Floor (floor_id=1)
                ['floor_id' => 1, 'room_id' => null, 'count' => 2],  // Reception desks (no room)
                ['floor_id' => 1, 'room_id' => 1, 'count' => 2],     // Open Office A
                ['floor_id' => 1, 'room_id' => 3, 'count' => 1],     // Meeting Room Boardroom
                
                // First Floor (floor_id=2)
                ['floor_id' => 2, 'room_id' => null, 'count' => 2],  // Executive lobby (no room)
                ['floor_id' => 2, 'room_id' => 4, 'count' => 2],     // Executive Suite
                
                // Second Floor (floor_id=3)
                ['floor_id' => 3, 'room_id' => 7, 'count' => 2],     // Backend Development
                ['floor_id' => 3, 'room_id' => 8, 'count' => 2],     // Frontend Development
                ['floor_id' => 3, 'room_id' => 9, 'count' => 2],     // QA Testing Lab
                ['floor_id' => 3, 'room_id' => 10, 'count' => 2],    // DevOps & Infrastructure
                
                // Third Floor (floor_id=4)
                ['floor_id' => 4, 'room_id' => 11, 'count' => 2],    // Design Studio
                ['floor_id' => 4, 'room_id' => 12, 'count' => 3],    // Marketing Department
                ['floor_id' => 4, 'room_id' => 13, 'count' => 3],    // Sales Team
            ];

            // Add or update desks from API
            $deskIndex = 0;
            foreach ($distributions as $dist) {
                for ($i = 0; $i < $dist['count'] && $deskIndex < count($apiDeskIds); $i++) {
                    $deskId = $apiDeskIds[$deskIndex];
                    $apiData = $this->deskApiService->getDeskData($deskId);
                    $deskName = $apiData['config']['name'] ?? "Desk {$deskId}";

                    $desk = Desk::where('desk_id', $deskId)->first();

                    if ($desk) {
                        if ($desk->is_removed_from_api || $desk->name !== $deskName) {
                            $desk->update([
                                'is_removed_from_api' => false,
                                'name' => $deskName,
                                'room_id' => $dist['room_id'],
                                'floor_id' => $dist['floor_id'],
                            ]);
                        }
                    } else {
                        Desk::create([
                            'desk_id' => $deskId,
                            'name' => $deskName,
                            'room_id' => $dist['room_id'],
                            'floor_id' => $dist['floor_id'],
                            'is_removed_from_api' => false,
                        ]);
                    }

                    $deskIndex++;
                }
            }

            return $apiDeskIds;
        } catch (\Exception $e) {
            echo "⚠️  Error syncing desks from API: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * Create test desks if API is unavailable
     * 
     * @param array $rooms Rooms to assign desks to
     * @return array Array of desk IDs
     */
    private function createTestDesks(array $rooms = []): array
    {
        echo "ℹ️  Creating test desks for demonstration...\n";

        $testDesks = [
            ['id' => '00:ec:eb:50:c2:c8', 'name' => 'Reception Desk 1'],
            ['id' => '00:ec:eb:50:c2:c9', 'name' => 'Reception Desk 2'],
            ['id' => '00:ec:eb:50:c2:ca', 'name' => 'Open Office A1'],
            ['id' => '00:ec:eb:50:c2:cb', 'name' => 'Open Office A2'],
            ['id' => '00:ec:eb:50:c2:cc', 'name' => 'Meeting Table 1'],
            ['id' => '00:ec:eb:50:c2:cd', 'name' => 'Executive Lobby 1'],
            ['id' => '00:ec:eb:50:c2:ce', 'name' => 'Executive Lobby 2'],
            ['id' => '00:ec:eb:50:c2:cf', 'name' => 'Executive Desk 1'],
            ['id' => '00:ec:eb:50:c2:d0', 'name' => 'Executive Desk 2'],
            ['id' => '00:ec:eb:50:c2:d1', 'name' => 'Backend Dev 1'],
            ['id' => '00:ec:eb:50:c2:d2', 'name' => 'Backend Dev 2'],
            ['id' => '00:ec:eb:50:c2:d3', 'name' => 'Frontend Dev 1'],
            ['id' => '00:ec:eb:50:c2:d4', 'name' => 'Frontend Dev 2'],
            ['id' => '00:ec:eb:50:c2:d5', 'name' => 'QA Desk 1'],
            ['id' => '00:ec:eb:50:c2:d6', 'name' => 'QA Desk 2'],
            ['id' => '00:ec:eb:50:c2:d7', 'name' => 'DevOps Desk 1'],
            ['id' => '00:ec:eb:50:c2:d8', 'name' => 'DevOps Desk 2'],
            ['id' => '00:ec:eb:50:c2:d9', 'name' => 'Design Desk 1'],
            ['id' => '00:ec:eb:50:c2:da', 'name' => 'Design Desk 2'],
            ['id' => '00:ec:eb:50:c2:db', 'name' => 'Marketing Desk 1'],
            ['id' => '00:ec:eb:50:c2:dc', 'name' => 'Marketing Desk 2'],
            ['id' => '00:ec:eb:50:c2:dd', 'name' => 'Marketing Desk 3'],
            ['id' => '00:ec:eb:50:c2:de', 'name' => 'Sales Desk 1'],
            ['id' => '00:ec:eb:50:c2:df', 'name' => 'Sales Desk 2'],
            ['id' => '00:ec:eb:50:c2:e0', 'name' => 'Sales Desk 3'],
        ];

        $distributions = [
            // Ground Floor (floor_id=1)
            ['floor_id' => 1, 'room_id' => null, 'count' => 2],  // Reception desks (no room)
            ['floor_id' => 1, 'room_id' => 1, 'count' => 2],     // Open Office A
            ['floor_id' => 1, 'room_id' => 3, 'count' => 1],     // Meeting Room Boardroom
            
            // First Floor (floor_id=2)
            ['floor_id' => 2, 'room_id' => null, 'count' => 2],  // Executive lobby (no room)
            ['floor_id' => 2, 'room_id' => 4, 'count' => 2],     // Executive Suite
            
            // Second Floor (floor_id=3)
            ['floor_id' => 3, 'room_id' => 7, 'count' => 2],     // Backend Development
            ['floor_id' => 3, 'room_id' => 8, 'count' => 2],     // Frontend Development
            ['floor_id' => 3, 'room_id' => 9, 'count' => 2],     // QA Testing Lab
            ['floor_id' => 3, 'room_id' => 10, 'count' => 2],    // DevOps & Infrastructure
            
            // Third Floor (floor_id=4)
            ['floor_id' => 4, 'room_id' => 11, 'count' => 2],    // Design Studio
            ['floor_id' => 4, 'room_id' => 12, 'count' => 3],    // Marketing Department
            ['floor_id' => 4, 'room_id' => 13, 'count' => 3],    // Sales Team
        ];

        $deskIds = [];
        $deskIndex = 0;

        foreach ($distributions as $dist) {
            for ($i = 0; $i < $dist['count'] && $deskIndex < count($testDesks); $i++) {
                $desk = $testDesks[$deskIndex];

                Desk::updateOrCreate(
                    ['desk_id' => $desk['id']],
                    [
                        'name' => $desk['name'],
                        'room_id' => $dist['room_id'],
                        'floor_id' => $dist['floor_id'],
                        'is_removed_from_api' => false,
                    ]
                );
                $deskIds[] = $desk['id'];
                $deskIndex++;
            }
        }

        echo "✓ Created " . count($testDesks) . " test desks\n";
        return $deskIds;
    }

    /**
     * Create desk metrics for a specific desk
     * 
     * @param string $deskId Desk ID
     * @param int $count Number of metrics to create
     * @param bool $preferStanding If true, create more standing metrics
     */
    private function createDeskMetrics(string $deskId, int $count = 30, bool $preferStanding = false): void
    {
        for ($i = 0; $i < $count; $i++) {
            if ($preferStanding) {
                // 65% standing, 35% sitting
                $isSitting = fake()->numberBetween(1, 100) > 65;
            } else {
                // 50% sitting, 50% standing
                $isSitting = fake()->numberBetween(1, 100) > 50;
            }

            $heightMm = $isSitting
                ? fake()->numberBetween(700, 850)
                : fake()->numberBetween(1000, 1200);

            DeskMetric::create([
                'desk_id' => $deskId,
                'height_mm' => $heightMm,
                'is_sitting' => $isSitting,
                'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            ]);
        }
    }
}
