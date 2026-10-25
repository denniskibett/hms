<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityPackage;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * Starting room types, conference venues and conference packages.
 * Only adds what is missing, so it is safe to run again and never
 * overwrites prices the hotel has changed.
 */
class RoomAndFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            ['code' => 'executive', 'name' => 'Executive rooms', 'base_rate' => 7000, 'capacity' => 2, 'bed_type' => 'king'],
            ['code' => 'deluxe', 'name' => 'Deluxe rooms', 'base_rate' => 5000, 'capacity' => 2, 'bed_type' => 'queen'],
            ['code' => 'standard', 'name' => 'Standard rooms', 'base_rate' => 4000, 'capacity' => 2, 'bed_type' => 'double'],
        ];
        foreach ($roomTypes as $type) {
            RoomType::firstOrCreate(['code' => $type['code']], $type);
        }

        // Venues. Hire is included in the per-person conference packages, so the daily rate is 0.
        $venues = [
            ['code' => 'main_hall', 'name' => 'Main Conference Hall', 'capacity' => 50, 'base_rate' => 0,
                'description' => 'Theatre, classroom or U-shape layout.',
                'amenities' => ['Wi-Fi', 'PA system', 'Microphones', 'Flip chart', 'Screen']],
            ['code' => 'boardroom', 'name' => 'Boardroom', 'capacity' => 15, 'base_rate' => 0,
                'description' => 'Board table for meetings and interviews.',
                'amenities' => ['Wi-Fi', 'Screen', 'Whiteboard']],
            ['code' => 'garden', 'name' => 'Garden', 'capacity' => 100, 'base_rate' => 0,
                'description' => 'Outdoor space for team building, receptions and parties.',
                'amenities' => ['PA system', 'Parking']],
        ];
        foreach ($venues as $venue) {
            Facility::firstOrCreate(['code' => $venue['code']], $venue + ['status' => 'available']);
        }

        // Conference packages, sold with any venue
        $inclusions = 'Mints, flip charts, PA system, stationery, mineral water';
        $packages = [
            ['name' => 'Lunch Only', 'price' => 1800, 'charge_basis' => 'per_person', 'inclusions' => $inclusions],
            ['name' => "Half Day (Lunch and 10 O'Clock Tea)", 'price' => 2000, 'charge_basis' => 'per_person', 'inclusions' => $inclusions],
            ['name' => 'Full Day (Lunch and 2 Teas)', 'price' => 2500, 'charge_basis' => 'per_person', 'inclusions' => $inclusions],
            ['name' => 'Projector Hire per day', 'price' => 3000, 'charge_basis' => 'per_unit', 'inclusions' => null],
        ];
        foreach ($packages as $package) {
            FacilityPackage::firstOrCreate(['name' => $package['name']], $package + ['facility_id' => null, 'is_active' => true]);
        }
    }
}
