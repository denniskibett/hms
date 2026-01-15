<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoomAndFacilitySeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Seed Room Types
        DB::table('room_types')->insert([
            [
                'name' => 'Executive rooms',
                'code' => 'executive',
                'base_rate' => 7000,
                'capacity' => 2,
                'bed_type' => 'king',
                'description' => null,
                'amenities' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Deluxe rooms',
                'code' => 'deluxe',
                'base_rate' => 5000,
                'capacity' => 2,
                'bed_type' => 'queen',
                'description' => null,
                'amenities' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Standard rooms',
                'code' => 'standard',
                'base_rate' => 4000,
                'capacity' => 2,
                'bed_type' => 'double',
                'description' => null,
                'amenities' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // Seed Facilities (Conference rates)
        DB::table('facilities')->insert([
            [
                'name' => 'Lunch Only',
                'code' => 'lunch_only',
                'capacity' => 50,
                'base_rate' => 1800,
                'description' => null,
                'amenities' => json_encode(["Mints","Flip-charts","P.A system","Stationery","Mineral water"]),
                'status' => 'available',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Half Day (Lunch and 10 O\'Clock Tea)',
                'code' => 'half_day',
                'capacity' => 50,
                'base_rate' => 2000,
                'description' => null,
                'amenities' => json_encode(["Mints","Flip-charts","P.A system","Stationery","Mineral water"]),
                'status' => 'available',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Full Day (Lunch and 2 Teas)',
                'code' => 'full_day',
                'capacity' => 50,
                'base_rate' => 2500,
                'description' => null,
                'amenities' => json_encode(["Mints","Flip-charts","P.A system","Stationery","Mineral water"]),
                'status' => 'available',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Projector Hire per day',
                'code' => 'projector_hire',
                'capacity' => 1,
                'base_rate' => 3000,
                'description' => null,
                'amenities' => json_encode(["Mints","Flip-charts","P.A system","Stationery","Mineral water"]),
                'status' => 'available',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
