<?php

namespace App\Models;

use App\Enums\StayStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Countries\Package\Countries;

/**
 * A guest's profile. Name, phone and email live on the linked User
 * (stays.guest_id points at users.id, not at this table).
 */
class Guest extends Model
{
    use HasFactory;

    protected $table = 'guest_profiles';

    protected $fillable = [
        'user_id',
        'id_type',
        'id_number',
        'nationality',
        'address',
        'emergency_contact',
        'preferences',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
        'preferences' => 'array',
    ];

    public const ID_TYPES = [
        'national_id' => 'National ID',
        'passport' => 'Passport',
        'driving_license' => 'Driving Licence',
        'other' => 'Other',
    ];

    public const PREFERENCE_TAGS = [
        'Quiet Room', 'High Floor', 'Ground Floor', 'Near Elevator', 'Accessible',
        'Twin Beds', 'Extra Pillows', 'Non Smoking', 'Late Check Out', 'Early Check In',
        'Vegetarian', 'Halal',
    ];

    // ---- Relationships --------------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stays()
    {
        return $this->hasMany(Stay::class, 'guest_id', 'user_id');
    }

    // ---- Scopes ---------------------------------------------------------

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn ($q) => $q
            ->whereHas('user', fn ($u) => $u
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"))
            ->orWhere('id_number', 'like', "%{$term}%"));
    }

    public function scopeInHouse(Builder $query): Builder
    {
        return $query->whereHas('stays', fn ($q) => $q->inHouse());
    }

    /**
     * Has a reservation that hasn't checked in yet (today, overdue or future).
     */
    public function scopeExpected(Builder $query): Builder
    {
        return $query->whereHas('stays', fn ($q) => $q->whereIn('status', [StayStatus::Reserved->value, StayStatus::Booked->value]));
    }

    // ---- Helpers --------------------------------------------------------

    public function getNameAttribute(): string
    {
        return $this->user->name ?? 'Guest';
    }

    public function getIdTypeLabelAttribute(): ?string
    {
        return self::ID_TYPES[$this->id_type] ?? null;
    }

    /**
     * Values for the add/edit modal.
     */
    public function formData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'phone' => $this->user?->phone,
            'email' => $this->user?->email,
            'dob' => $this->user?->dob,
            'id_type' => $this->id_type,
            'id_number' => $this->id_number,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'emergency_contact' => [
                'name' => $this->emergency_contact['name'] ?? '',
                'phone' => $this->emergency_contact['phone'] ?? '',
                'relationship' => $this->emergency_contact['relationship'] ?? '',
            ],
            'preferences' => [
                'tags' => $this->preferences['tags'] ?? [],
                'allergies' => $this->preferences['allergies'] ?? '',
                'notes' => $this->preferences['notes'] ?? '',
            ],
        ];
    }

    /**
     * Country names for the nationality field.
     */
    public static function countries(): array
    {
        return Cache::rememberForever('guest_countries', function () {
            try {
                return (new Countries)->all()
                    ->map(fn ($c) => $c->name->common ?? null)
                    ->filter()
                    ->sort()
                    ->values()
                    ->all();
            } catch (\Throwable) {
                return ['Kenya', 'Uganda', 'Tanzania', 'Rwanda', 'United Kingdom', 'United States'];
            }
        });
    }
}
