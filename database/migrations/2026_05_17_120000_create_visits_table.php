<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            $table->enum('status', ['waiting','with_nurse','with_doctor','pharmacy','completed','cancelled'])
                  ->default('waiting');
            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'checked_in_at']);
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
