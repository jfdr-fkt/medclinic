<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('dosage');
            $table->string('form_other_note')->nullable()->after('image_path');
        });
    }
    public function down(): void {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'form_other_note']);
        });
    }
};
