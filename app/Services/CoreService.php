<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditTrail;
use App\Models\Notification;
use App\Models\Department;
use App\Models\GuestProfile;
use App\Models\StaffProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotification;
use Illuminate\Contracts\Mail\Mailable;

class CoreService
{
    // ==================== USER MANAGEMENT ====================
    
    /**
     * Create user with appropriate profile.
     */
    public function createUser(array $data, string $roleName = 'guest'): User
    {
        return DB::transaction(function () use ($data, $roleName) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password123'),
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);
            
            // Assign role
            $role = Role::where('name', $roleName)->firstOrFail();
            $user->roles()->attach($role->id);
            
            // Create profile based on role
            $this->createUserProfile($user, $data, $roleName);
            
            // Log the action
            $this->log($user->id, 'user_created', "User {$user->name} created with role: {$roleName}");
            
            return $user->load('roles');
        });
    }
    
    private function createUserProfile(User $user, array $data, string $roleName): void
    {
        switch ($roleName) {
            case 'guest':
                $user->guestProfile()->create([
                    'id_type' => $data['id_type'] ?? 'national_id',
                    'id_number' => $data['id_number'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'address' => $data['address'] ?? null,
                ]);
                break;
                
            default: // All staff roles
                $user->staffProfile()->create([
                    'department_id' => $this->getDepartmentIdForRole($roleName),
                    'salary' => $data['salary'] ?? 0,
                    'hire_date' => $data['hire_date'] ?? now(),
                    'employment_status' => $data['employment_status'] ?? 'probation',
                    'bank_name' => $data['bank_name'] ?? null,
                    'account_number' => $data['account_number'] ?? null,
                ]);
                break;
        }
    }
    
    private function getDepartmentIdForRole(string $roleName): ?int
    {
        $departmentMap = [
            'admin' => 'MGT',
            'manager' => 'MGT',
            'finance' => 'FIN',
            'receptionist' => 'REC',
            'housekeeping' => 'HK',
            'kitchen' => 'KIT',
            'procurement' => 'PROC',
            'hr' => 'HR',
        ];
        
        return Department::where('code', $departmentMap[$roleName] ?? 'MGT')->value('id');
    }
    
    /**
     * Assign roles to user.
     */
    public function assignRoles(User $user, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        $user->roles()->sync($roles);
        
        $this->log($user->id, 'roles_updated', "Roles updated for {$user->name}");
    }
    
    /**
     * Check user permission.
     */
    public function userHasPermission(User $user, string $permission): bool
    {
        $rolePermissions = $this->getRolePermissions();
        
        foreach ($user->roles as $role) {
            $permissions = $rolePermissions[$role->name] ?? [];
            if (in_array('all', $permissions) || in_array($permission, $permissions)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function getRolePermissions(): array
    {
        return [
            'admin' => ['all'],
            'manager' => ['view_reports', 'manage_staff', 'approve_payments', 'manage_inventory', 'view_financials', 'view_dashboard'],
            'finance' => ['view_financials', 'process_payments', 'manage_invoices', 'generate_reports', 'view_dashboard'],
            'receptionist' => ['create_guests', 'check_in', 'check_out', 'create_bookings', 'process_payments', 'view_guests', 'view_dashboard'],
            'housekeeping' => ['view_tasks', 'update_tasks', 'view_rooms', 'update_room_status', 'view_dashboard'],
            'kitchen' => ['view_orders', 'update_orders', 'manage_menu', 'update_inventory', 'view_dashboard'],
            'procurement' => ['manage_inventory', 'create_purchase_orders', 'manage_suppliers', 'update_stock', 'view_dashboard'],
            'hr' => ['manage_staff', 'process_payroll', 'approve_leave', 'view_attendance', 'view_dashboard'],
            'guest' => ['view_bookings', 'make_payments', 'request_services', 'view_dashboard'],
        ];
    }
    
    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName)
    {
        return User::whereHas('roles', function ($query) use ($roleName) {
            $query->where('name', $roleName);
        })->get();
    }
    
    /**
     * Get all staff users (excluding guests).
     */
    public function getAllStaff()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', '!=', 'guest');
        })->get();
    }
    
    /**
     * Get all guests.
     */
    public function getAllGuests()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'guest');
        })->get();
    }
    
    // ==================== NOTIFICATION MANAGEMENT ====================
    
    /**
     * Send multi-channel notification.
     */
    public function sendNotification(User $user, string $type, array $data): void
    {
        // Email
        if ($user->email && ($user->notify_via_email ?? true)) {
            $this->sendEmail($user, $type, $data);
        }
        
        // SMS
        if ($user->phone && ($user->notify_via_sms ?? true)) {
            $this->sendSms($user, $type, $data);
        }
        
        // In-app
        $this->sendInApp($user, $type, $data);
        
        $this->log($user->id, 'notification_sent', "{$type} notification sent to {$user->name}");
    }
    
    private function sendEmail(User $user, string $type, array $data): void
    {
        $template = $this->getEmailTemplate($type, $data);
        
        Mail::to($user->email)->queue(new GenericNotification(
            $template['subject'],
            $template['body'],
            $template['data']
        ));
    }
    
    private function sendSms(User $user, string $type, array $data): void
    {
        // Implement SMS gateway integration
        // $message = $this->getSmsTemplate($type, $data);
        // SMSGateway::send($user->phone, $message);
    }
    
    private function sendInApp(User $user, string $type, array $data): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $this->getNotificationTitle($type),
            'message' => $this->getNotificationMessage($type, $data),
            'data' => $data,
            'read_at' => null,
        ]);
    }
    
    // ==================== AUDIT & LOGGING ====================
    
    /**
     * Log activity.
     */
    public function log(?int $userId, string $action, string $description = '', array $data = []): AuditTrail
    {
        return AuditTrail::create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'new_values' => $data ? json_encode($data) : null,
        ]);
    }
    
    /**
     * Get audit logs with filters.
     */
    public function getAuditLogs(array $filters = [])
    {
        $query = AuditTrail::with('user');
        
        // Apply filters
        foreach (['user_id', 'action', 'date_from', 'date_to'] as $filter) {
            if (isset($filters[$filter])) {
                match($filter) {
                    'date_from' => $query->where('created_at', '>=', $filters[$filter]),
                    'date_to' => $query->where('created_at', '<=', $filters[$filter]),
                    default => $query->where($filter, $filters[$filter]),
                };
            }
        }
        
        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 50);
    }
    
    // ==================== HELPER METHODS ====================
    
    private function getEmailTemplate(string $type, array $data): array
    {
        $templates = [
            'booking_confirmation' => [
                'subject' => 'Booking Confirmation - The Willis Hotel',
                'body' => 'emails.booking.confirmation',
            ],
            'checkin_reminder' => [
                'subject' => 'Check-in Reminder',
                'body' => 'emails.booking.reminder',
            ],
            'payment_receipt' => [
                'subject' => 'Payment Receipt',
                'body' => 'emails.payment.receipt',
            ],
            'task_assigned' => [
                'subject' => 'New Task Assigned',
                'body' => 'emails.task.assigned',
            ],
            'invoice_generated' => [
                'subject' => 'New Invoice Generated',
                'body' => 'emails.invoice.generated',
            ],
        ];
        
        return [
            'subject' => $templates[$type]['subject'] ?? 'Notification from The Willis Hotel',
            'body' => $templates[$type]['body'] ?? 'emails.generic',
            'data' => $data,
        ];
    }
    
    private function getNotificationTitle(string $type): string
    {
        return match($type) {
            'booking_confirmation' => 'Booking Confirmed',
            'checkin_reminder' => 'Check-in Reminder',
            'payment_receipt' => 'Payment Received',
            'task_assigned' => 'New Task',
            'invoice_generated' => 'New Invoice',
            default => 'Notification',
        };
    }
    
    private function getNotificationMessage(string $type, array $data): string
    {
        return match($type) {
            'booking_confirmation' => "Your booking #{$data['booking_id']} has been confirmed",
            'checkin_reminder' => "Check-in reminder for room {$data['room_number']}",
            'payment_receipt' => "Payment of KSH {$data['amount']} received",
            'task_assigned' => "You have been assigned a new task: {$data['task_title']}",
            'invoice_generated' => "New invoice #{$data['invoice_number']} generated",
            default => 'You have a new notification',
        };
    }
}