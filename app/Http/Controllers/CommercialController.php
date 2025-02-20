<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sondage;
use App\Models\User;

class CommercialController extends Controller
{

    public function index()
    {
        $sondages = Sondage::where('status', 'pending')->get(); 
        return view('commercials.index', compact('sondages'));
    }


    public function assign(Sondage $sondage)
    {
        $sondeurs = User::where('role', 'sondeur')->get(); 
        return view('commercials.assign', compact('sondage', 'sondeurs'));
    }


    public function storeAssignment(Request $request, Sondage $sondage)
    {
        $request->validate([
            'sondeur_id' => 'required|exists:users,id'
        ]);

        $sondage->update(['sondeur_id' => $request->sondeur_id, 'status' => 'assigned']);

        return redirect()->route('commercials.index')->with('success', 'Sondeur assigné avec succès.');
    }
}

