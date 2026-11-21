<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Facilities become a conference & events module:
 * - facilities         = venues (hall, boardroom, garden) with a hire rate per day
 * - facility_packages  = conference packages priced per person per day (Full Day,
 *                        Half Day…) or per unit per day (projector hire). A package
 *                        with no facility_id can be sold with any venue.
 * - facility_bookings  = an event: client, venue, dates, pax, status; has its own invoice
 * - facility_booking_items = what is sold on the event (venue hire, packages, extras)
 *
 * The rows already in `facilities` were really packages, so they move across.
 * facility_allocations (hourly hire tied to a stay) was never used and is replaced.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facility_packages', function (Blueprint $table) {
            $table->dropForeign('facility_packages_facility_id_foreign');
        });

        Schema::table('facility_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('facility_id')->nullable()->change();
            $table->string('charge_basis', 20)->default('per_person')->after('price');
            $table->dropColumn('duration_hours');
            $table->foreign('facility_id')->references('id')->on('facilities')->cascadeOnDelete();
        });

        if (DB::table('facility_allocations')->exists()) {
            throw new RuntimeException('facility_allocations has data; move it to facility_bookings by hand before running this migration.');
        }

        // Existing "facilities" are conference packages: move them
        foreach (DB::table('facilities')->get() as $row) {
            DB::table('facility_packages')->insert([
                'facility_id' => null,
                'name' => $row->name,
                'price' => $row->base_rate,
                'charge_basis' => str_contains(strtolower($row->name), 'hire') ? 'per_unit' : 'per_person',
                'inclusions' => $row->description,
                'is_active' => $row->status === 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('tasks')->whereNotNull('facility_id')->update(['facility_id' => null]);
        DB::table('facilities')->delete();

        Schema::drop('facility_allocations');

        Schema::table('facilities', function (Blueprint $table) {
            $table->decimal('base_rate', 12)->default(0)->comment('Venue hire per day')->change();
        });

        Schema::create('facility_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->restrictOnDelete();
            $table->foreignId('stay_id')->nullable()->constrained('stays')->nullOnDelete();
            $table->string('title');
            $table->string('client_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 30);
            $table->string('contact_email')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('pax');
            $table->string('status', 20)->default('tentative');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['facility_id', 'start_date', 'end_date']);
            $table->index('status');
        });

        Schema::create('facility_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->string('charge_basis', 20);
            $table->decimal('quantity', 10)->default(1);
            $table->unsignedInteger('days')->default(1);
            $table->decimal('unit_price', 12);
            $table->timestamps();
        });

        // Events have their own invoice, not tied to a room stay
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('stay_id')->nullable()->change();
            $table->foreignId('facility_booking_id')->nullable()->after('stay_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facility_booking_id');
        });

        Schema::dropIfExists('facility_booking_items');
        Schema::dropIfExists('facility_bookings');

        Schema::create('facility_allocations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('stay_id')->index('facility_allocations_stay_id_foreign');
            $table->unsignedBigInteger('facility_id');
            $table->unsignedBigInteger('package_id')->nullable()->index('facility_allocations_package_id_foreign');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->decimal('rate_applied', 12);
            $table->enum('status', ['booked', 'confirmed', 'in_use', 'completed', 'cancelled'])->default('booked');
            $table->timestamps();

            $table->index(['facility_id', 'start_time', 'end_time']);
            $table->foreign(['facility_id'])->references(['id'])->on('facilities')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['package_id'])->references(['id'])->on('facility_packages')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['stay_id'])->references(['id'])->on('stays')->onUpdate('restrict')->onDelete('cascade');
        });

        Schema::table('facility_packages', function (Blueprint $table) {
            $table->dropColumn('charge_basis');
            $table->integer('duration_hours')->default(8);
        });
    }
};
