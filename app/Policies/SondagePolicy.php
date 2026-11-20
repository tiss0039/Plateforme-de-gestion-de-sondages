<?php

namespace App\Policies;

use App\Enums\SondageStatus;
use App\Models\Sondage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Règles d'accès aux sondages.
 *
 * - Le créateur gère son sondage tant qu'il n'est pas ouvert aux réponses.
 * - Seul un utilisateur ayant la permission « validate-sondage »
 *   (administrateur) valide ou refuse un sondage : un client ne peut donc pas
 *   valider son propre sondage.
 * - Le créateur ne répond pas à son propre sondage.
 */
class SondagePolicy
{
    public function create(User $user): bool
    {
        return $user->can('create-sondage');
    }

    public function view(User $user, Sondage $sondage): bool
    {
        return $this->estCreateur($user, $sondage) || $user->can('validate-sondage');
    }

    public function update(User $user, Sondage $sondage): Response
    {
        if (! $this->estCreateur($user, $sondage)) {
            return Response::deny('Seul le créateur du sondage peut le modifier.');
        }

        if (! $sondage->statut->estModifiable() || $sondage->participations()->exists()) {
            return Response::deny('Un sondage ouvert ou terminé ne peut plus être modifié.');
        }

        return Response::allow();
    }

    public function delete(User $user, Sondage $sondage): Response
    {
        if (! $this->estCreateur($user, $sondage) && ! $user->can('delete-sondage')) {
            return Response::deny('Vous ne pouvez pas supprimer ce sondage.');
        }

        if ($sondage->statut === SondageStatus::EN_COURS) {
            return Response::deny('Clôturez le sondage avant de le supprimer.');
        }

        return Response::allow();
    }

    public function moderate(User $user, Sondage $sondage): Response
    {
        if (! $user->can('validate-sondage')) {
            return Response::deny('Seul un administrateur peut valider ou refuser un sondage.');
        }

        if ($sondage->statut !== SondageStatus::EN_ATTENTE) {
            return Response::deny('Seul un sondage en attente peut être validé ou refusé.');
        }

        return Response::allow();
    }

    public function open(User $user, Sondage $sondage): Response
    {
        if (! $this->estCreateur($user, $sondage)) {
            return Response::deny('Seul le créateur du sondage peut l\'ouvrir.');
        }

        if ($sondage->statut !== SondageStatus::VALIDE) {
            return Response::deny('Le sondage doit être validé par un administrateur avant d\'être ouvert.');
        }

        if ($sondage->date_fin->lt(today())) {
            return Response::deny('La date de fin est dépassée : modifiez les dates avant d\'ouvrir le sondage.');
        }

        return Response::allow();
    }

    public function close(User $user, Sondage $sondage): Response
    {
        if (! $this->estCreateur($user, $sondage) && ! $user->can('validate-sondage')) {
            return Response::deny('Vous ne pouvez pas clôturer ce sondage.');
        }

        if ($sondage->statut !== SondageStatus::EN_COURS) {
            return Response::deny('Seul un sondage en cours peut être clôturé.');
        }

        return Response::allow();
    }

    public function participate(User $user, Sondage $sondage): Response
    {
        if (! $user->can('execute-sondage')) {
            return Response::deny('Votre compte n\'a pas l\'autorisation de répondre aux sondages.');
        }

        if ($this->estCreateur($user, $sondage)) {
            return Response::deny('Vous ne pouvez pas répondre à votre propre sondage.');
        }

        if (! $sondage->estOuvertAuxReponses()) {
            return Response::deny('Ce sondage n\'est pas ouvert aux réponses.');
        }

        return Response::allow();
    }

    public function viewResults(User $user, Sondage $sondage): Response
    {
        if (! $this->estCreateur($user, $sondage) && ! $user->can('view-resultats')) {
            return Response::deny('Seul le créateur du sondage ou un administrateur peut consulter les résultats.');
        }

        if (! $sondage->statut->aDesResultats()) {
            return Response::deny('Les résultats sont disponibles une fois le sondage ouvert.');
        }

        return Response::allow();
    }

    private function estCreateur(User $user, Sondage $sondage): bool
    {
        return $user->id === $sondage->user_id;
    }
}
