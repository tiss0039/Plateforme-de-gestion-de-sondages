<?php

namespace App\Http\Controllers;

use App\Enums\SondageStatus;
use App\Models\Sondage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sondages = $user->sondages()
            ->withCount(['questions', 'participations'])
            ->latest()
            ->orderByDesc('id') // ordre stable pour des sondages créés à la même seconde
            ->get();

        // Sondages auxquels l'utilisateur peut encore répondre.
        $sondagesOuverts = $user->can('execute-sondage')
            ? Sondage::ouvertsAuxReponses()
                ->where('user_id', '!=', $user->id)
                ->whereDoesntHave('participations', fn ($query) => $query->where('user_id', $user->id))
                ->count()
            : 0;

        $enAttenteDeValidation = $user->can('validate-sondage')
            ? Sondage::where('statut', SondageStatus::EN_ATTENTE)->count()
            : null;

        return view('dashboard', compact('sondages', 'sondagesOuverts', 'enAttenteDeValidation'));
    }
}
