<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dispense_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('set null');
            $table->foreignId('dispensed_by')->constrained('users')->onDelete('cascade');
            $table->integer('quantity');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('dispense_logs'); }
};
