<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','clinic_head','doctor','pharmacist','nurse','secretary','assistant') NOT NULL DEFAULT 'assistant'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','doctor','nurse','assistant') NOT NULL DEFAULT 'assistant'");
    }
};
