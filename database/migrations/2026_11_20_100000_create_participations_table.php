<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Participation d'un utilisateur à un sondage : une seule par utilisateur et
 * par sondage, garantie par un index unique.
 *
 * Migration additive : les anciennes tables reponses et stockage_reponses ne
 * sont pas modifiées.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sondage_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sondage_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};
