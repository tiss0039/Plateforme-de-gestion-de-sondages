<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Liste des sondages ouverts et réponse d'un participant. */
class ParticipationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sondages = Sondage::ouvertsAuxReponses()
            ->where('user_id', '!=', $user->id)
            ->withCount('questions')
            ->withExists(['participations as deja_repondu' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderBy('date_fin')
            ->orderBy('id')
            ->get();

        return view('participations.index', [
            'sondages' => $sondages,
            'peutRepondre' => $user->can('execute-sondage'),
        ]);
    }

    public function create(Request $request, Sondage $sondage): View|RedirectResponse
    {
        if ($sondage->aRepondu($request->user())) {
            return redirect()->route('participations.index')
                ->with('info', 'Vous avez déjà répondu à ce sondage. Merci pour votre participation.');
        }

        Gate::authorize('participate', $sondage);

        $sondage->load('questions.options');

        return view('participations.create', compact('sondage'));
    }

    public function store(Request $request, Sondage $sondage): RedirectResponse
    {
        $user = $request->user();

        if ($sondage->aRepondu($user)) {
            return redirect()->route('participations.index')
                ->with('info', 'Vous avez déjà répondu à ce sondage. Une seule réponse est acceptée par participant.');
        }

        Gate::authorize('participate', $sondage);

        $questions = $sondage->questions()->with('options')->get();
        $reponses = $request->validate(...$this->reglesDeValidation($questions))['reponses'];

        try {
            DB::transaction(function () use ($sondage, $user, $questions, $reponses) {
                $participation = $sondage->participations()->create(['user_id' => $user->id]);

                foreach ($questions as $question) {
                    $valeur = $reponses[$question->id];
                    $lignes = match ($question->question_type) {
                        'text' => [['texte' => trim($valeur)]],
                        'multiple' => [['option_id' => (int) $valeur]],
                        'checkbox' => collect($valeur)->unique()->map(fn ($id) => ['option_id' => (int) $id])->all(),
                    };

                    foreach ($lignes as $ligne) {
                        $participation->reponses()->create($ligne + ['question_id' => $question->id]);
                    }
                }
            });
        } catch (UniqueConstraintViolationException) {
            // Deux envois simultanés : l'index unique (sondage, utilisateur)
            // garantit qu'une seule participation est enregistrée.
            return redirect()->route('participations.index')
                ->with('info', 'Vous avez déjà répondu à ce sondage. Une seule réponse est acceptée par participant.');
        }

        return redirect()->route('participations.index')
            ->with('success', "Merci ! Votre réponse au sondage « {$sondage->titre} » est enregistrée.");
    }

    /**
     * Toutes les questions sont obligatoires ; une option choisie doit
     * appartenir à la question concernée.
     *
     * @return array{0: array, 1: array, 2: array}
     */
    private function reglesDeValidation($questions): array
    {
        $regles = ['reponses' => ['required', 'array']];
        $messages = [];
        $attributs = [];

        foreach ($questions as $numero => $question) {
            /** @var Question $question */
            $cle = "reponses.{$question->id}";
            $optionsValides = $question->options->pluck('id')->all();
            $attributs[$cle] = 'question '.($numero + 1);
            $attributs["$cle.*"] = 'question '.($numero + 1);

            switch ($question->question_type) {
                case 'text':
                    $regles[$cle] = ['required', 'string', 'max:2000'];
                    break;
                case 'multiple':
                    $regles[$cle] = ['required', 'integer', Rule::in($optionsValides)];
                    break;
                case 'checkbox':
                    $regles[$cle] = ['required', 'array', 'min:1'];
                    $regles["$cle.*"] = ['integer', 'distinct', Rule::in($optionsValides)];
                    break;
            }

            $messages["$cle.required"] = 'Veuillez répondre à la question '.($numero + 1).'.';
            $messages["$cle.in"] = 'Le choix de la question '.($numero + 1).' n\'est pas valide.';
            $messages["$cle.*.in"] = 'Un choix de la question '.($numero + 1).' n\'est pas valide.';
        }

        return [$regles, $messages, $attributs];
    }
}
