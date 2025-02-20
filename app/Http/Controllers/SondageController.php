<?php

namespace App\Http\Controllers;

use App\Models\Sondage;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use App\Enums\SondageStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SondageController extends Controller
{

    public function create()
    {
        return view('create');
    }

   
    public function store(Request $request)
    {

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'questions' => 'required|array|min:1',
            'questions.*.title' => 'required|string|max:255',  
            'questions.*.question' => 'required|string|max:255',
            'questions.*.options' => 'exclude_if:questions.*.type_question,text|required|array|min:1',
            'questions.*.options.*' => 'required|string|min:1',
        ]);

        
        $sondage = Sondage::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'start_date' => $validated['date_debut'],
            'end_date' => $validated['date_fin'],
            'statut' => SondageStatus::EN_ATTENTE, 
            'user_id' => Auth::id(),
        ]);

        foreach ($validated['questions'] as $questionData) {
            $question = $sondage->questions()->create([
                'title' => $questionData['title'],  
                'question_type' => $questionData['type_question'],
                'question' => $questionData['question'],
            ]);

  
            if (in_array($questionData['type_question'], ['multiple', 'checkbox'])) {
                foreach ($questionData['options'] as $option) {
                    $question->options()->create(['option' => $option]);
                }
            }
        }


        return redirect()->route('dashboard')->with('success', 'Sondage créé avec succès !');
    }


    public function show(Sondage $sondage)
    {
        $this->authorize('view', $sondage);
        return view('clients.sondages.show', compact('sondage'));
    }


    public function edit(Sondage $sondage)
    {
        $this->authorize('update', $sondage);
        return view('clients.sondages.edit', compact('sondage'));
    }


    public function update(Request $request, Sondage $sondage)
    {
        $this->authorize('update', $sondage);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $sondage->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Sondage mis à jour avec succès !');
    }


    public function destroy(Sondage $sondage)
    {
        $this->authorize('delete', $sondage);
        
        $sondage->delete();
        return redirect()->route('dashboard')
            ->with('success', 'Sondage supprimé avec succès !');
    }
}
