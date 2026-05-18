<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE shifts MODIFY shift_type ENUM('morning','afternoon','night','on_call','custom') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE shifts SET shift_type='morning' WHERE shift_type='custom'");
        DB::statement("ALTER TABLE shifts MODIFY shift_type ENUM('morning','afternoon','night','on_call') NOT NULL");
    }
};
