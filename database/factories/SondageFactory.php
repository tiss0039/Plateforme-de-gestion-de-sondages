<?php

namespace Database\Factories;

use App\Enums\SondageStatus;
use App\Models\Option;
use App\Models\Question;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sondage> */
class SondageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titre' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'date_debut' => today()->subDay(),
            'date_fin' => today()->addMonth(),
            'statut' => SondageStatus::EN_ATTENTE,
            'user_id' => User::factory(),
        ];
    }

    public function statut(SondageStatus $statut): static
    {
        return $this->state(['statut' => $statut]);
    }

    public function enCours(): static
    {
        return $this->statut(SondageStatus::EN_COURS);
    }

    /** Une question libre, une à choix unique et une à choix multiples. */
    public function avecQuestions(): static
    {
        return $this->afterCreating(function (Sondage $sondage) {
            Question::factory()->for($sondage)->create(['title' => 'Commentaire', 'question_type' => 'text']);

            $unique = Question::factory()->for($sondage)->create(['title' => 'Satisfaction', 'question_type' => 'multiple']);
            Option::factory()->for($unique)->createMany([['contenu' => 'Oui'], ['contenu' => 'Non']]);

            $multiple = Question::factory()->for($sondage)->create(['title' => 'Langages', 'question_type' => 'checkbox']);
            Option::factory()->for($multiple)->createMany([['contenu' => 'PHP'], ['contenu' => 'Java'], ['contenu' => 'Python']]);
        });
    }
}
