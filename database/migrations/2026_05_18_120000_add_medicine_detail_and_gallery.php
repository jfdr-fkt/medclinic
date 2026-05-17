<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            // Richer clinical fields shown on the medicine details page.
            $table->text('indications')->nullable()->after('description');
            $table->text('dosage_instructions')->nullable()->after('indications');
            $table->text('side_effects')->nullable()->after('dosage_instructions');
            $table->text('warnings')->nullable()->after('side_effects');
            $table->text('storage_instructions')->nullable()->after('warnings');
            // Gallery — JSON list of additional image paths (up to 5).
            // Kept on the row to avoid a join for what is always ≤5 small thumbnails.
            $table->json('gallery_paths')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'indications', 'dosage_instructions', 'side_effects',
                'warnings', 'storage_instructions', 'gallery_paths',
            ]);
        });
    }
};
