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
        Schema::table('itineraries', function (Blueprint $table) {
            $table->string('transportation_type')->nullable();
            $table->string('flight_duration')->nullable();
            $table->decimal('airfare_min', 10, 2)->nullable();
            $table->decimal('airfare_max', 10, 2)->nullable();
            $table->string('booking_website')->nullable();
            $table->string('road_distance')->nullable();
            $table->string('road_duration')->nullable();
            $table->string('road_type')->nullable();
            $table->string('languages')->nullable();
            $table->string('peak_travel_times')->nullable();
            $table->string('travel_agency')->nullable();
            $table->decimal('agency_fees', 10, 2)->nullable();
            $table->text('travel_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itineraries', function (Blueprint $table) {
            $table->dropColumn([
                'transportation_type',
                'flight_duration',
                'airfare_min',
                'airfare_max',
                'booking_website',
                'road_distance',
                'road_duration',
                'road_type',
                'languages',
                'peak_travel_times',
                'travel_agency',
                'agency_fees',
                'travel_notes'
            ]);
        });
    }
};
