<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('brand_names')->nullable()->after('generic_name');
            $table->string('dosage_form')->nullable()->after('dosage');
        });
    }
    public function down(): void {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['brand_names', 'dosage_form']);
        });
    }
};
