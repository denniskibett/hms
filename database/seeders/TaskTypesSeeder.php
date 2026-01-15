<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\TaskType;
use Illuminate\Database\Seeder;

class TaskTypesSeeder extends Seeder
{
    public function run(): void
    {
        // Get departments
        $housekeepingDept = Department::where('code', 'HK')->first();
        $maintenanceDept = Department::where('code', 'MNT')->first();
        $kitchenDept = Department::where('code', 'KIT')->first();
        $receptionDept = Department::where('code', 'REC')->first();
        
        $taskTypes = [
            // Housekeeping tasks
            [
                'name' => 'Check-in Cleaning',
                'code' => 'CHECKIN_CLEAN',
                'department_id' => $housekeepingDept->id ?? null,
                'category' => 'cleaning',
                'description' => 'Thorough cleaning before guest check-in',
                'default_checklist' => [
                    ['item' => 'Change bed linens', 'completed' => false],
                    ['item' => 'Clean bathroom', 'completed' => false],
                    ['item' => 'Vacuum/mop floor', 'completed' => false],
                    ['item' => 'Dust surfaces', 'completed' => false],
                    ['item' => 'Restock amenities', 'completed' => false],
                ],
                'default_estimated_minutes' => 45,
                'default_estimated_cost' => 500,
                'requires_room' => true,
                'requires_inventory' => true,
            ],
            [
                'name' => 'Daily Cleaning',
                'code' => 'DAILY_CLEAN',
                'department_id' => $housekeepingDept->id ?? null,
                'category' => 'cleaning',
                'description' => 'Daily room cleaning for occupied rooms',
                'default_checklist' => [
                    ['item' => 'Make bed', 'completed' => false],
                    ['item' => 'Empty trash', 'completed' => false],
                    ['item' => 'Clean bathroom', 'completed' => false],
                    ['item' => 'Restock towels', 'completed' => false],
                    ['item' => 'Vacuum floor', 'completed' => false],
                ],
                'default_estimated_minutes' => 20,
                'default_estimated_cost' => 200,
                'requires_room' => true,
                'requires_inventory' => true,
            ],
            [
                'name' => 'Check-out Cleaning',
                'code' => 'CHECKOUT_CLEAN',
                'department_id' => $housekeepingDept->id ?? null,
                'category' => 'cleaning',
                'description' => 'Deep cleaning after guest check-out',
                'default_checklist' => [
                    ['item' => 'Strip bed linens', 'completed' => false],
                    ['item' => 'Deep clean bathroom', 'completed' => false],
                    ['item' => 'Clean all surfaces', 'completed' => false],
                    ['item' => 'Vacuum and mop floor', 'completed' => false],
                    ['item' => 'Check amenities', 'completed' => false],
                ],
                'default_estimated_minutes' => 60,
                'default_estimated_cost' => 800,
                'requires_room' => true,
                'requires_inventory' => true,
            ],
            [
                'name' => 'Deep Cleaning',
                'code' => 'DEEP_CLEAN',
                'department_id' => $housekeepingDept->id ?? null,
                'category' => 'cleaning',
                'description' => 'Weekly/Monthly deep cleaning',
                'default_checklist' => [
                    ['item' => 'Clean windows', 'completed' => false],
                    ['item' => 'Clean curtains', 'completed' => false],
                    ['item' => 'Clean under furniture', 'completed' => false],
                    ['item' => 'Clean vents', 'completed' => false],
                    ['item' => 'Deep clean bathroom', 'completed' => false],
                ],
                'default_estimated_minutes' => 120,
                'default_estimated_cost' => 1500,
                'requires_room' => true,
                'requires_inventory' => true,
            ],
            
            // Kitchen tasks
            [
                'name' => 'Kitchen Cleaning',
                'code' => 'KITCHEN_CLEAN',
                'department_id' => $kitchenDept->id ?? null,
                'category' => 'kitchen',
                'description' => 'Daily kitchen cleaning',
                'default_checklist' => [
                    ['item' => 'Clean countertops', 'completed' => false],
                    ['item' => 'Clean appliances', 'completed' => false],
                    ['item' => 'Mop floor', 'completed' => false],
                    ['item' => 'Take out trash', 'completed' => false],
                    ['item' => 'Restock supplies', 'completed' => false],
                ],
                'default_estimated_minutes' => 60,
                'default_estimated_cost' => 300,
                'requires_room' => false,
                'requires_inventory' => true,
            ],
            
            // Maintenance tasks
            [
                'name' => 'Room Maintenance',
                'code' => 'ROOM_MAINT',
                'department_id' => $maintenanceDept->id ?? null,
                'category' => 'maintenance',
                'description' => 'General room maintenance',
                'default_checklist' => [
                    ['item' => 'Check plumbing', 'completed' => false],
                    ['item' => 'Check electrical', 'completed' => false],
                    ['item' => 'Check furniture', 'completed' => false],
                    ['item' => 'Check appliances', 'completed' => false],
                ],
                'default_estimated_minutes' => 30,
                'default_estimated_cost' => 0,
                'requires_room' => true,
                'requires_inventory' => false,
            ],
            
            // Reception tasks
            [
                'name' => 'Guest Check-in',
                'code' => 'GUEST_CHECKIN',
                'department_id' => $receptionDept->id ?? null,
                'category' => 'reception',
                'description' => 'Process guest check-in',
                'default_checklist' => [
                    ['item' => 'Verify ID', 'completed' => false],
                    ['item' => 'Process payment', 'completed' => false],
                    ['item' => 'Assign room', 'completed' => false],
                    ['item' => 'Issue key', 'completed' => false],
                    ['item' => 'Explain amenities', 'completed' => false],
                ],
                'default_estimated_minutes' => 10,
                'default_estimated_cost' => 0,
                'requires_room' => false,
                'requires_inventory' => false,
            ],
        ];
        
        foreach ($taskTypes as $type) {
            TaskType::create($type);
        }
    }
}