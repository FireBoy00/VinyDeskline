<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\SensorMetric;
use Carbon\Carbon;

class SensorMetricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $data = [];

        // Generate 24 hours of data, every 15 minutes
        for ($i = 0; $i < 24 * 4; $i++) {
            $time = $now->copy()->subMinutes($i * 15);
            
            // Realistic fluctuations
            $temp = 21 + sin($i / 10) * 2 + rand(-5, 5) / 10;
            $humidity = 45 + cos($i / 8) * 5 + rand(-10, 10) / 10;
            $light = max(0, 400 + sin($i / 5) * 300 + rand(-50, 50));

            $data[] = [
                'temperature' => $temp,
                'humidity' => $humidity,
                'light' => $light,
                'recorded_at' => $time,
                'created_at' => $time,
                'updated_at' => $time,
            ];
        }

        SensorMetric::insert($data);
    }
}
