<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Tests and new installs. Live: database/schema/2026_11_21_company_accounts_and_night_audit.sql, by hand.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('kra_pin', 20)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);        // 0 = no limit
            $table->unsignedSmallInteger('payment_terms_days')->default(30);
            $table->decimal('rate_discount_percent', 5, 2)->default(0); // negotiated room rate
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('name');
        });

        Schema::table('stays', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('guest_id')->index();
        });
        Schema::table('facility_bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('stay_id')->index();
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('company_id')->nullable()->after('kitchen_order_id')->index();
        });

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE stays MODIFY status ENUM('booked','checked_in','checked_out','cancelled','reserved','no_show') NOT NULL DEFAULT 'reserved'");
        }

        Schema::create('night_audits', function (Blueprint $table) {
            $table->id();
            $table->date('business_date')->unique();
            $table->unsignedBigInteger('run_by')->nullable();   // null = scheduled run
            $table->timestamp('run_at');
            $table->json('summary');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_audits');
        Schema::table('invoices', fn (Blueprint $t) => $t->dropColumn('company_id'));
        Schema::table('facility_bookings', fn (Blueprint $t) => $t->dropColumn('company_id'));
        Schema::table('stays', fn (Blueprint $t) => $t->dropColumn('company_id'));
        Schema::dropIfExists('companies');
    }
};
