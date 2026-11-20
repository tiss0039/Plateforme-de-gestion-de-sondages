<?php

namespace App\Http\Controllers;

use App\Enums\SondageStatus;
use App\Http\Requests\SondageRequest;
use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SondageController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Sondage::class);

        return view('sondages.create', [
            'sondage' => new Sondage,
            'questions' => $this->questionsDuFormulaire(),
        ]);
    }

    public function store(SondageRequest $request): RedirectResponse
    {
        $this->authorize('create', Sondage::class);

        // Sondage, questions et options sont enregistrés en une seule
        // transaction : en cas d'erreur, rien n'est enregistré partiellement.
        $sondage = DB::transaction(function () use ($request) {
            $sondage = $request->user()->sondages()->create(
                $request->donneesSondage() + ['statut' => SondageStatus::EN_ATTENTE]
            );
            $this->enregistrerQuestions($sondage, $request->donneesQuestions());

            return $sondage;
        });

        return redirect()->route('sondages.show', $sondage)
            ->with('success', 'Sondage créé. Il sera ouvert aux réponses après validation par un administrateur.');
    }

    public function show(Sondage $sondage): View
    {
        $this->authorize('view', $sondage);

        $sondage->load(['questions.options', 'user', 'moderateur'])->loadCount('participations');

        return view('sondages.show', compact('sondage'));
    }

    public function edit(Sondage $sondage): View
    {
        $this->authorize('update', $sondage);

        $sondage->load('questions.options');

        return view('sondages.edit', [
            'sondage' => $sondage,
            'questions' => $this->questionsDuFormulaire($sondage),
        ]);
    }

    public function update(SondageRequest $request, Sondage $sondage): RedirectResponse
    {
        $this->authorize('update', $sondage);

        // Toute modification doit être de nouveau validée par un administrateur.
        DB::transaction(function () use ($request, $sondage) {
            $sondage->update($request->donneesSondage() + [
                'statut' => SondageStatus::EN_ATTENTE,
                'motif_refus' => null,
                'moderateur_id' => null,
                'modere_le' => null,
            ]);

            // Un sondage modifiable n'a encore reçu aucune réponse (vérifié par
            // la politique d'accès) : ses questions peuvent être remplacées.
            $sondage->questions()->delete();
            $this->enregistrerQuestions($sondage, $request->donneesQuestions());
        });

        return redirect()->route('sondages.show', $sondage)
            ->with('success', 'Sondage modifié. Il doit de nouveau être validé par un administrateur.');
    }

    public function destroy(Sondage $sondage): RedirectResponse
    {
        $this->authorize('delete', $sondage);

        $sondage->delete();

        return redirect()->route('dashboard')->with('success', 'Sondage supprimé.');
    }

    public function open(Sondage $sondage): RedirectResponse
    {
        $this->authorize('open', $sondage);

        $sondage->update(['statut' => SondageStatus::EN_COURS]);

        return redirect()->route('sondages.show', $sondage)
            ->with('success', 'Sondage ouvert : les participants peuvent maintenant répondre.');
    }

    public function close(Sondage $sondage): RedirectResponse
    {
        $this->authorize('close', $sondage);

        $sondage->update(['statut' => SondageStatus::TERMINE]);

        return redirect()->route('sondages.show', $sondage)
            ->with('success', 'Sondage clôturé : il n\'accepte plus de réponses.');
    }

    /** @param  array<int, array{title: string, question: ?string, question_type: string, options: string[]}>  $questions */
    private function enregistrerQuestions(Sondage $sondage, array $questions): void
    {
        foreach ($questions as $donnees) {
            $question = $sondage->questions()->create([
                'title' => $donnees['title'],
                'question' => $donnees['question'],
                'question_type' => $donnees['question_type'],
            ]);

            foreach ($donnees['options'] as $contenu) {
                $question->options()->create(['contenu' => $contenu]);
            }
        }
    }

    /**
     * Questions affichées dans le formulaire : saisie précédente en cas
     * d'erreur de validation, sinon questions existantes, sinon une question vide.
     */
    private function questionsDuFormulaire(?Sondage $sondage = null): array
    {
        $anciennes = old('questions');

        if (is_array($anciennes)) {
            $texte = fn ($valeur) => is_scalar($valeur) ? (string) $valeur : '';

            return collect($anciennes)->filter(fn ($question) => is_array($question))->map(fn (array $question) => [
                'title' => $texte($question['title'] ?? ''),
                'question' => $texte($question['question'] ?? ''),
                'type_question' => $texte($question['type_question'] ?? 'text'),
                'options' => array_values(array_map($texte, (array) ($question['options'] ?? []))),
            ])->values()->all();
        }

        if ($sondage && $sondage->questions->isNotEmpty()) {
            return $sondage->questions->map(fn (Question $question) => [
                'title' => $question->title,
                'question' => (string) $question->question,
                'type_question' => $question->question_type,
                'options' => $question->options->pluck('contenu')->all(),
            ])->all();
        }

        return [['title' => '', 'question' => '', 'type_question' => 'text', 'options' => []]];
    }
}
