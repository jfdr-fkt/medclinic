<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('emergency_contact_2_name', 120)->nullable()->after('emergency_contact_phone');
            $table->string('emergency_contact_2_phone', 60)->nullable()->after('emergency_contact_2_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact_2_name', 'emergency_contact_2_phone']);
        });
    }
};
