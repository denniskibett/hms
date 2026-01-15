<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'avatar',
        'country',
        'city',
        'state',
        'postal_code',
        'tax_id',
        'social',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'social' => 'array',
    ];

    /**
     * Get the attributes that should be appended.
     */
    protected $appends = [
        'social_links',
        'social_usernames',
        'full_address',
    ];

    // ==================== RELATIONSHIPS ====================


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Guest profile (if user is a guest).
     */
    public function guest()
    {
        return $this->hasOne(Guest::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    /**
     * Stays created by this user (for guests).
     */
    public function stays()
    {
        return $this->hasMany(Stay::class, 'guest_id');
    }

    /**
     * Stays created by this user (for receptionists).
     */
    public function createdStays()
    {
        return $this->hasMany(Stay::class, 'created_by');
    }

    /**
     * Payments received by this user.
     */
    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    /**
     * Kitchen orders placed by this user.
     */
    public function kitchenOrders()
    {
        return $this->hasMany(KitchenOrder::class, 'placed_by');
    }

    /**
     * Tasks assigned to this user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Purchase orders requested by this user.
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'requested_by');
    }

    /**
     * Approved purchase orders.
     */
    public function approvedPurchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'approved_by');
    }

    /**
     * Leave requests submitted by this user.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Approved leave requests.
     */
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Shift assignments.
     */
    public function shiftAssignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    /**
     * Payroll records.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Approved payroll records.
     */
    public function approvedPayrolls()
    {
        return $this->hasMany(Payroll::class, 'approved_by');
    }

    /**
     * Audit trails created by this user.
     */
    public function auditTrails()
    {
        return $this->hasMany(AuditTrail::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope for users with a specific role.
     */
    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    /**
     * Scope for active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for guests.
     */
    public function scopeGuests($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'guest');
        });
    }

    /**
     * Scope for staff members (excluding guests).
     */
    public function scopeStaff($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', '!=', 'guest');
        });
    }

    // ==================== ATTRIBUTES ====================

    /**
     * Get social links with proper URLs.
     */
    public function getSocialLinksAttribute()
    {
        $social = $this->social ?: [];
        
        $links = [];
        
        if (!empty($social['facebook'])) {
            $links['facebook'] = $this->getSocialUrl($social['facebook'], 'https://facebook.com/');
        }
        
        if (!empty($social['twitter'])) {
            $links['twitter'] = $this->getSocialUrl($social['twitter'], 'https://twitter.com/');
        }
        
        if (!empty($social['instagram'])) {
            $links['instagram'] = $this->getSocialUrl($social['instagram'], 'https://instagram.com/');
        }
        
        if (!empty($social['linkedin'])) {
            $links['linkedin'] = $this->getSocialUrl($social['linkedin'], 'https://linkedin.com/in/');
        }
        
        return $links;
    }

    /**
     * Extract username from URL or return as-is.
     */
    public function getSocialUsernamesAttribute()
    {
        $social = $this->social ?: [];
        $usernames = [];
        
        foreach ($social as $platform => $value) {
            $usernames[$platform] = $this->extractUsername($value);
        }
        
        return $usernames;
    }

    /**
     * Get full address.
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]);
        
        return implode(', ', $parts);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return $this->roles()->whereIn('name', $role)->exists();
        }
        
        return $this->roles()->where('name', $role)->exists();
    }


    public function hasAnyRole(array $roles)
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }


    public function isGuest()
    {
        return $this->hasRole('guest');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }


    public function isReceptionist()
    {
        return $this->hasRole('receptionist');
    }


    public function isFinance()
    {
        return $this->hasRole('finance');
    }

    /**
     * Check if user is housekeeping staff.
     */
    public function isHousekeeping()
    {
        return $this->hasRole('housekeeping');
    }

    /**
     * Check if user is kitchen staff.
     */
    public function isKitchen()
    {
        return $this->hasRole('kitchen');
    }

    /**
     * Check if user is procurement staff.
     */
    public function isProcurement()
    {
        return $this->hasRole('procurement');
    }

    /**
     * Check if user is HR staff.
     */
    public function isHR()
    {
        return $this->hasRole('hr');
    }

    /**
     * Check if user is a manager.
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Helper to extract username from URL.
     */
    private function extractUsername($url)
    {
        if (empty($url)) {
            return '';
        }
        
        if (!str_contains($url, '.') && !str_contains($url, '/')) {
            return $url;
        }
        
        $patterns = [
            'facebook' => [
                '/^https?:\/\/(www\.)?facebook\.com\//',
                '/^https?:\/\/fb\.com\//'
            ],
            'twitter' => [
                '/^https?:\/\/(www\.)?twitter\.com\//',
                '/^https?:\/\/x\.com\//'
            ],
            'instagram' => [
                '/^https?:\/\/(www\.)?instagram\.com\//'
            ],
            'linkedin' => [
                '/^https?:\/\/(www\.)?linkedin\.com\/in\//'
            ]
        ];
        
        foreach ($patterns as $platformPatterns) {
            foreach ($platformPatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    return preg_replace($pattern, '', $url);
                }
            }
        }
        
        return $url;
    }

    /**
     * Helper to create full URL from username.
     */
    private function getSocialUrl($value, $baseUrl)
    {
        if (empty($value)) {
            return null;
        }
        
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }

    /**
     * Prepare social data for storage.
     */
    public function prepareSocialData($data)
    {
        $social = [];
        
        foreach ($data as $platform => $value) {
            if (empty($value)) {
                continue;
            }
            
            $value = trim($value);
            
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $social[$platform] = $value;
            } else {
                $social[$platform] = $this->cleanUsername($value);
            }
        }
        
        return $social;
    }

    public function shifts()
    {
        return $this->belongsToMany(
            Shift::class, 
            'shift_assignments',
            'user_id', 
            'shift_id'
        )->withPivot('date', 'status')->withTimestamps();
    }

    private function cleanUsername($username)
    {
        return ltrim(trim($username), '@');
    }
}