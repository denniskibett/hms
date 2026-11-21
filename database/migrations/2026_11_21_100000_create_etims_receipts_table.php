<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tests and new installs. Live: database/schema/2026_11_21_etims_receipts.sql, applied by hand.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etims_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->unique();
            $table->unsignedInteger('invc_no')->unique();       // our sequential eTIMS invoice number
            $table->string('status', 20)->default('pending');  // pending | sent | failed
            $table->string('rcpt_no', 40)->nullable();
            $table->string('tot_rcpt_no', 40)->nullable();
            $table->string('intrl_data', 120)->nullable();
            $table->string('rcpt_sign', 120)->nullable();
            $table->string('sdc_id', 40)->nullable();
            $table->string('mrc_no', 40)->nullable();
            $table->timestamp('receipt_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->json('request')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etims_receipts');
    }
};
