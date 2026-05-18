<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rebrand the seeded admin from a clinical title to the proper IT/Operations role.
        DB::table('users')
            ->where('email', 'admin@clinic.com')
            ->update([
                'name' => 'Sarah Chen',
                'specialization' => 'IT & Operations Admin',
            ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@clinic.com')
            ->update([
                'name' => 'Dr. Sarah Chen',
                'specialization' => 'Internal Medicine',
            ]);
    }
};
