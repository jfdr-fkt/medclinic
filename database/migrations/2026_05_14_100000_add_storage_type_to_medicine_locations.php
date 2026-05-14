<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_locations', function (Blueprint $table) {
            $table->string('storage_type', 50)->default('Cabinet')->after('id');
        });

        DB::table('medicine_locations')->update(['storage_type' => 'Cabinet']);
    }

    public function down(): void
    {
        Schema::table('medicine_locations', function (Blueprint $table) {
            $table->dropColumn('storage_type');
        });
    }
};
