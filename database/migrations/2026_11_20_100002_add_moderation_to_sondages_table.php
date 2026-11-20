<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Informations de validation par un administrateur (colonnes facultatives).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sondages', function (Blueprint $table) {
            $table->text('motif_refus')->nullable();
            $table->foreignId('moderateur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('modere_le')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sondages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderateur_id');
            $table->dropColumn(['motif_refus', 'modere_le']);
        });
    }
};
