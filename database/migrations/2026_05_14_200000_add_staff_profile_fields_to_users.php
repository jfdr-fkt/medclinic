<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->date('hire_date')->nullable()->after('date_of_birth');
            $table->string('address', 255)->nullable()->after('hire_date');
            $table->string('emergency_contact_name', 120)->nullable()->after('address');
            $table->string('emergency_contact_phone', 60)->nullable()->after('emergency_contact_name');
            $table->string('license_number', 80)->nullable()->after('emergency_contact_phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'hire_date',
                'address',
                'emergency_contact_name',
                'emergency_contact_phone',
                'license_number',
            ]);
        });
    }
};
