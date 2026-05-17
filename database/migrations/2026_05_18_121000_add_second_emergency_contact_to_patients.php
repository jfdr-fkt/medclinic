<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Real-world clinics require a backup contact in case the primary
            // can't be reached (e.g. on a call, lost their phone). Both are
            // required from the Add Patient form now.
            $table->string('emergency_contact_2_name')->nullable()->after('emergency_contact_phone');
            $table->string('emergency_contact_2_phone')->nullable()->after('emergency_contact_2_name');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['emergency_contact_2_name', 'emergency_contact_2_phone']);
        });
    }
};
