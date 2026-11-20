<?php

namespace App\Http\Requests;

use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validation d'un sondage et de ses questions (création et modification).
 * L'autorisation (politique d'accès) est vérifiée avant la validation.
 */
class SondageRequest extends FormRequest
{
    public const MAX_QUESTIONS = 30;

    public const MAX_OPTIONS = 10;

    public function authorize(): Response
    {
        $sondage = $this->route('sondage');

        return $sondage instanceof Sondage
            ? Gate::inspect('update', $sondage)
            : Gate::inspect('create', Sondage::class);
    }

    public function rules(): array
    {
        return [
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'questions' => ['required', 'array', 'min:1', 'max:'.self::MAX_QUESTIONS],
            'questions.*' => ['array'],
            'questions.*.title' => ['required', 'string', 'max:255'],
            'questions.*.question' => ['nullable', 'string', 'max:1000'],
            'questions.*.type_question' => ['required', Rule::in(array_keys(Question::TYPES))],
            'questions.*.options' => ['exclude_if:questions.*.type_question,text', 'required', 'array', 'min:2', 'max:'.self::MAX_OPTIONS],
            'questions.*.options.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Les options d'une même question doivent être différentes (la règle
     * « distinct » de Laravel comparerait les options de toutes les questions).
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ((array) $this->input('questions', []) as $index => $question) {
                    $options = collect($question['options'] ?? [])
                        ->filter(fn ($option) => is_string($option))
                        ->map(fn (string $option) => Str::lower(trim($option)));

                    if (($question['type_question'] ?? null) !== 'text' && $options->duplicates()->isNotEmpty()) {
                        $validator->errors()->add(
                            "questions.$index.options",
                            'Les options de la question '.($index + 1).' doivent être différentes.'
                        );
                    }
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'titre' => 'titre',
            'date_debut' => 'date de début',
            'date_fin' => 'date de fin',
            'questions' => 'questions',
            'questions.*.title' => 'intitulé de la question',
            'questions.*.question' => 'précision de la question',
            'questions.*.type_question' => 'type de question',
            'questions.*.options' => 'options',
            'questions.*.options.*' => 'option',
        ];
    }

    public function messages(): array
    {
        return [
            'questions.required' => 'Ajoutez au moins une question.',
            'questions.min' => 'Ajoutez au moins une question.',
            'questions.*.options.required' => 'Une question à choix doit proposer au moins deux options.',
            'questions.*.options.min' => 'Une question à choix doit proposer au moins deux options.',
        ];
    }

    /** Données validées, prêtes à enregistrer. */
    public function donneesSondage(): array
    {
        return $this->safe()->only(['titre', 'description', 'date_debut', 'date_fin']);
    }

    /** Questions validées, options nettoyées. */
    public function donneesQuestions(): array
    {
        return collect($this->validated('questions'))->map(fn (array $question) => [
            'title' => trim($question['title']),
            'question' => isset($question['question']) ? trim($question['question']) ?: null : null,
            'question_type' => $question['type_question'],
            'options' => $question['type_question'] === 'text'
                ? []
                : array_map('trim', array_values($question['options'] ?? [])),
        ])->values()->all();
    }
}
