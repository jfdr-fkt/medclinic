<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('name');
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->text('medical_history')->nullable();
            $table->foreignId('assigned_nurse_id')->nullable()->constrained('users');
            $table->foreignId('assigned_doctor_id')->nullable()->constrained('users');
            $table->timestamp('last_visit')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('patients'); }
};