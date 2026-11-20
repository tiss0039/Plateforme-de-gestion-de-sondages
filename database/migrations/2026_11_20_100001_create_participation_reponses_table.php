<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Réponses d'une participation : une ligne par réponse libre, par choix
 * unique ou par case cochée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participation_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('texte')->nullable();
            $table->timestamps();

            $table->index(['question_id', 'option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participation_reponses');
    }
};
