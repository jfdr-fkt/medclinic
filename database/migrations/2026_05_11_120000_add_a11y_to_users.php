<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('font_size', ['sm', 'md', 'lg', 'xl'])->default('md')->after('theme');
            $table->boolean('colorblind_mode')->default(false)->after('font_size');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['font_size', 'colorblind_mode']);
        });
    }
};
