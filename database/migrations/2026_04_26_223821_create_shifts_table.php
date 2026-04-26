<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('shift_type', ['morning', 'afternoon', 'night', 'on_call']);
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('shifts'); }
};