<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Real clinic distinction: a patient who pre-booked vs a walk-in who showed up
            // with a complaint. Both flow through the same status pipeline; the chip is
            // shown in the queue so the front desk and clinical staff know which is which.
            $table->enum('visit_type', ['appointment', 'walk_in'])
                  ->default('walk_in')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropColumn('visit_type');
        });
    }
};
