<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->comment('System/Company name');
            $table->string('logo')->nullable()->comment('System logo file path');
            $table->string('favicon')->nullable()->comment('Favicon file path');
            $table->string('slogan')->nullable()->comment('System slogan or tagline');
            $table->string('timezone')->default('UTC')->comment('System default timezone');
            $table->string('date_format')->default('d-m-Y')->comment('Default date format');
            $table->string('time_format')->default('H:i:s')->comment('Default time format');
            $table->string('currency')->default('KES')->comment('Default currency');
            $table->string('currency_symbol')->default('$')->comment('Currency symbol');
            $table->string('primary_color')->nullable()->comment('Primary color for UI');
            $table->string('secondary_color')->nullable()->comment('Secondary color for UI');
            $table->string('contact_email')->nullable()->comment('System contact email');
            $table->string('contact_phone')->nullable()->comment('System contact phone');
            $table->string('address')->nullable()->comment('System physical address');
            $table->text('meta_description')->nullable()->comment('SEO meta description');
            $table->text('meta_keywords')->nullable()->comment('SEO meta keywords');
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->boolean('maintenance_mode')->default(false)->comment('System maintenance status');
            $table->integer('pagination_limit')->default(15)->comment('Default items per page');
            $table->text('custom_css')->nullable()->comment('Custom CSS styles');
            $table->text('custom_js')->nullable()->comment('Custom JavaScript');
            $table->json('settings')->nullable()->comment('Additional settings in JSON format');
            $table->timestamps();
        });

        // Insert default system configuration
        DB::table('system')->insert([
            'name' => 'Your System Name',
            'slogan' => 'Your system slogan here',
            'timezone' => 'UTC',
            'currency' => 'KES',
            'currency_symbol' => '$',
            'date_format' => 'd-m-Y',
            'time_format' => 'H:i:s',
            'pagination_limit' => 15,
            'maintenance_mode' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system');
    }
};