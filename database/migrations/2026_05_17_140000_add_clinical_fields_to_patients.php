<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->enum('sex', ['male','female','other'])->nullable()->after('date_of_birth');
            $table->enum('blood_type', ['A+','A-','B+','B-','AB+','AB-','O+','O-','unknown'])->nullable()->after('sex');
            $table->unsignedSmallInteger('height_cm')->nullable()->after('blood_type');
            $table->decimal('weight_kg', 5, 2)->nullable()->after('height_cm');
            $table->text('allergies')->nullable()->after('medical_history');
            $table->text('chronic_conditions')->nullable()->after('allergies');
            $table->string('emergency_contact_name', 120)->nullable()->after('chronic_conditions');
            $table->string('emergency_contact_phone', 60)->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'sex', 'blood_type', 'height_cm', 'weight_kg',
                'allergies', 'chronic_conditions',
                'emergency_contact_name', 'emergency_contact_phone',
            ]);
        });
    }
};
