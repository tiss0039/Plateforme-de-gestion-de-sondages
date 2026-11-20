<?php

namespace App\Http\Controllers;

use App\Enums\SondageStatus;
use App\Models\Sondage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Validation ou refus des sondages par un administrateur. */
class ModerationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('validate-sondage'), 403);

        $enAttente = Sondage::with('user')->withCount('questions')
            ->where('statut', SondageStatus::EN_ATTENTE)
            ->oldest('updated_at')
            ->orderBy('id')
            ->get();

        $recents = Sondage::with(['user', 'moderateur'])
            ->whereNotNull('modere_le')
            ->latest('modere_le')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('moderation.index', compact('enAttente', 'recents'));
    }

    public function approve(Request $request, Sondage $sondage): RedirectResponse
    {
        Gate::authorize('moderate', $sondage);

        $sondage->update([
            'statut' => SondageStatus::VALIDE,
            'motif_refus' => null,
            'moderateur_id' => $request->user()->id,
            'modere_le' => now(),
        ]);

        return back()->with('success', "Le sondage « {$sondage->titre} » est validé. Son créateur peut maintenant l'ouvrir.");
    }

    public function reject(Request $request, Sondage $sondage): RedirectResponse
    {
        Gate::authorize('moderate', $sondage);

        $validated = $request->validateWithBag('refus'.$sondage->id, [
            'motif_refus' => ['required', 'string', 'max:1000'],
        ], [], ['motif_refus' => 'motif du refus']);

        $sondage->update([
            'statut' => SondageStatus::REFUSE,
            'motif_refus' => $validated['motif_refus'],
            'moderateur_id' => $request->user()->id,
            'modere_le' => now(),
        ]);

        return back()->with('success', "Le sondage « {$sondage->titre} » est refusé.");
    }
}
