<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('medicines', function (Blueprint $table) {
            // Manual archive — set when a medicine is pulled from active circulation but kept
            // on the books (recall, damaged batch, awaiting destruction, off-site storage).
            // Auto-expired medicines (latestInventory.expiration_date in the past) show up
            // in the Archive view too but DON'T have archived_at set — that lets us tell the
            // two apart in the UI and unarchive only the manual ones.
            $table->timestamp('archived_at')->nullable()->after('description');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
            $table->string('archive_reason', 255)->nullable()->after('archived_by');
            // 'med_room' = pulled from active rotation but still in the in-clinic cabinet.
            // 'storage'  = moved to back-of-house storage or warehouse (off the medication floor).
            $table->enum('archive_location_type', ['med_room', 'storage'])->default('med_room')->after('archive_reason');
        });
    }
    public function down(): void {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('archived_by');
            $table->dropColumn(['archived_at', 'archive_reason', 'archive_location_type']);
        });
    }
};
