<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stockage_reponses', function (Blueprint $table) {
            $table->id();
            $table->foreignID('reponse_id')->constrained('reponses');
            $table->foreignID('question_id')->constrained('questions');
            $table->foreignID('option_id')->constrained('options');
            $table->text('texte_reponse')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockage__reponses');
    }
};
