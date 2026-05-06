<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', ['available', 'busy', 'away', 'offline'])->default('available')->after('is_active');
            $table->string('avatar')->nullable()->after('status');
            $table->text('bio')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'avatar', 'bio']);
        });
    }
};
