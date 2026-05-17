<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            // FK to users — nullable + nullOnDelete so a patient photo doesn't
            // disappear when the uploader account is removed (audit value).
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('path');                   // storage/app/public/patients/photos/...
            $table->string('caption')->nullable();    // e.g. "skin rash on left arm"
            $table->timestamps();
            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_images');
    }
};
