<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sondage_id' => Sondage::factory(),
            'title' => fake()->sentence(5),
            'question' => null,
            'question_type' => 'text',
        ];
    }
}
