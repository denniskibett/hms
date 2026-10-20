<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Guests rebuild:
 * - walk-in guests often have no email, and ID is captured at check-in, so both become optional
 * - emergency_contact becomes real JSON (old rows were double-encoded strings)
 * - preferences become { tags: [], allergies: '', notes: '' }
 * - guest users without a profile get one
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->enum('id_type', ['passport', 'national_id', 'driving_license', 'other'])->nullable()->change();
            $table->string('id_number')->nullable()->change();
            $table->text('emergency_contact')->nullable()->change();
        });

        foreach (DB::table('guest_profiles')->get() as $row) {
            $contact = $this->decode($row->emergency_contact);
            $contact = [
                'name' => $contact['name'] ?? null,
                'phone' => $contact['phone'] ?? null,
                'relationship' => $contact['relationship'] ?? null,
            ];
            $preferences = $this->decode($row->preferences);

            DB::table('guest_profiles')->where('id', $row->id)->update([
                'nationality' => $this->nationality($row->nationality),
                'emergency_contact' => array_filter($contact) ? json_encode($contact) : null,
                'preferences' => json_encode($this->preferences($preferences)),
            ]);
        }

        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->json('emergency_contact')->nullable()->change();
        });

        $guestRoleId = DB::table('roles')->where('name', 'guest')->value('id');
        if ($guestRoleId) {
            $missing = DB::table('role_user')
                ->where('role_id', $guestRoleId)
                ->whereNotIn('user_id', DB::table('guest_profiles')->select('user_id'))
                ->pluck('user_id');

            foreach ($missing as $userId) {
                DB::table('guest_profiles')->insert([
                    'user_id' => $userId,
                    'preferences' => json_encode(['tags' => [], 'allergies' => null, 'notes' => null]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('guest_profiles', function (Blueprint $table) {
            $table->string('emergency_contact')->nullable()->change();
        });
    }

    /**
     * Old rows hold JSON, JSON-in-a-JSON-string, or nothing.
     */
    private function decode(?string $value): array
    {
        for ($i = 0; $i < 3 && is_string($value); $i++) {
            $value = json_decode($value, true);
        }

        return is_array($value) ? $value : [];
    }

    private function nationality(?string $value): ?string
    {
        $value = trim((string) $value);

        return match (strtolower($value)) {
            '' => null,
            'kenyan', 'ke' => 'Kenya',
            'ug', 'ugandan' => 'Uganda',
            default => is_numeric($value) ? null : $value,
        };
    }

    /**
     * The old form stored tag lists per category (room_preference, entertainment, …);
     * they become one flat tag list.
     */
    private function preferences(array $old): array
    {
        $tags = collect($old)
            ->filter(fn ($value) => is_array($value))
            ->flatten()
            ->filter()
            ->map(fn ($tag) => Str::headline($tag))
            ->unique()
            ->values()
            ->all();

        return [
            'tags' => $tags,
            'allergies' => $old['allergies'] ?? null,
            'notes' => $old['other'] ?? $old['notes'] ?? null,
        ];
    }
};
