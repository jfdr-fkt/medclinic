<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->unsignedInteger('units_per_blister')->default(10)->after('storage_instructions');
            $table->unsignedInteger('blisters_per_pack')->default(10)->after('units_per_blister');
            $table->string('unit_label', 32)->default('tablet')->after('blisters_per_pack');
        });

        Schema::table('dispense_logs', function (Blueprint $table) {
            $table->string('unit', 16)->default('tablet')->after('quantity');
            $table->integer('quantity_in_units')->nullable()->after('unit');
            $table->boolean('is_return')->default(false)->after('quantity_in_units');
        });
    }

    public function down(): void
    {
        Schema::table('dispense_logs', function (Blueprint $table) {
            $table->dropColumn(['unit', 'quantity_in_units', 'is_return']);
        });
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['units_per_blister', 'blisters_per_pack', 'unit_label']);
        });
    }
};
