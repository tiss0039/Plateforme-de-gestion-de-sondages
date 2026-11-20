<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Résultats d'un sondage : effectifs et pourcentages par option, réponses libres. */
class ResultatController extends Controller
{
    public function show(Sondage $sondage): View
    {
        Gate::authorize('viewResults', $sondage);

        $sondage->loadCount('participations');
        $total = $sondage->participations_count;

        $questions = $sondage->questions()
            ->with([
                'options' => fn ($query) => $query->withCount('reponses'),
                'reponses' => fn ($query) => $query->whereNotNull('texte')->latest(),
            ])
            ->get()
            ->map(function (Question $question) use ($total) {
                $question->setAttribute('statistiques', $question->options->map(fn ($option) => [
                    'contenu' => $option->contenu,
                    'effectif' => $option->reponses_count,
                    // Pourcentage des participants : en choix multiples, le total peut dépasser 100 %.
                    'pourcentage' => $total > 0 ? round(100 * $option->reponses_count / $total, 1) : 0.0,
                ]));

                return $question;
            });

        return view('resultats.show', compact('sondage', 'questions', 'total'));
    }
}
