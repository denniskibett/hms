<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoomAndFacilitySeeder::class,
        ]);
        
        // Create Roles
        $roles = [
            ['name' => 'admin', 'description' => 'System Administrator'],
            ['name' => 'receptionist', 'description' => 'Front Desk Staff'],
            ['name' => 'housekeeping', 'description' => 'Cleaning Staff'],
            ['name' => 'kitchen', 'description' => 'Kitchen Staff'],
            ['name' => 'procurement', 'description' => 'Procurement Officer'],
            ['name' => 'hr', 'description' => 'Human Resources'],
            ['name' => 'guest', 'description' => 'Hotel Guest'],
            ['name' => 'manager', 'description' => 'Hotel Manager'],
        ];
        
        foreach ($roles as $role) {
            Role::create($role);
        }
        
        // Create Departments
        $departments = [
            ['name' => 'Reception', 'code' => 'REC'],
            ['name' => 'Housekeeping', 'code' => 'HK'],
            ['name' => 'Kitchen', 'code' => 'KIT'],
            ['name' => 'Procurement', 'code' => 'PROC'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Management', 'code' => 'MGT'],
        ];
        
        foreach ($departments as $dept) {
            Department::create($dept);
        }
        
        // Create Admin User
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        
        $admin->roles()->attach(Role::where('name', 'admin')->first());
    }
}