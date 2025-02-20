<?php

namespace App\Http\Controllers;

use App\Models\Reponse;
use App\Models\Question;
use Illuminate\Http\Request;

class ReponseController extends Controller
{
    public function index()
    {
        $reponses = Reponse::all();
        return view('reponses.index', compact('reponses'));
    }

    public function create()
    {
        $questions = Question::all();
        return view('reponses.create', compact('questions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required|string',
            'question_id' => 'required|exists:questions,id',
        ]);

        Reponse::create($request->all());

        return redirect()->route('reponses.index')->with('success', 'Réponse ajoutée.');
    }

    public function show(Reponse $reponse)
    {
        return view('reponses.show', compact('reponse'));
    }

    public function edit(Reponse $reponse)
    {
        $questions = Question::all();
        return view('reponses.edit', compact('reponse', 'questions'));
    }

    public function update(Request $request, Reponse $reponse)
    {
        $request->validate([
            'value' => 'required|string',
            'question_id' => 'required|exists:questions,id',
        ]);

        $reponse->update($request->all());

        return redirect()->route('reponses.index')->with('success', 'Réponse mise à jour.');
    }

    public function destroy(Reponse $reponse)
    {
        $reponse->delete();
        return redirect()->route('reponses.index')->with('success', 'Réponse supprimée.');
    }
}

