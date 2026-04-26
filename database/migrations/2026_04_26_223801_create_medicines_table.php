<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable()->unique();
            $table->foreignId('location_id')->constrained('medicine_locations');
            $table->enum('type', ['prescription', 'normal'])->default('normal');
            $table->text('description')->nullable();
            $table->string('dosage')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('medicines'); }
};