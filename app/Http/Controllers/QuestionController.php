<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Sondage;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::all();
        return view('questions.index', compact('questions'));
    }

    public function create()
    {
        $sondages = Sondage::all();
        return view('questions.create', compact('sondages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'question_type' => 'required|string',
            'sondage_id' => 'required|exists:sondages,id',
        ]);

        Question::create($request->all());

        return redirect()->route('questions.index')->with('success', 'Question créée avec succès.');
    }

    public function show(Question $question)
    {
        return view('questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $sondages = Sondage::all();
        return view('questions.edit', compact('question', 'sondages'));
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'question_type' => 'required|string',
            'sondage_id' => 'required|exists:sondages,id',
        ]);

        $question->update($request->all());

        return redirect()->route('questions.index')->with('success', 'Question mise à jour.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Question supprimée.');
    }
}

