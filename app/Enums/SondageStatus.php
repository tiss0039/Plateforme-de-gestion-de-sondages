<?php

namespace App\Enums;

/**
 * Cycle de vie d'un sondage :
 * En attente → Validé (ou Refusé) → En cours → Terminé.
 * Un sondage refusé ou validé qui est modifié repasse « En attente ».
 */
enum SondageStatus: string
{
    case EN_ATTENTE = 'En attente';
    case VALIDE = 'Validé';
    case REFUSE = 'Refusé';
    case EN_COURS = 'En cours';
    case TERMINE = 'Terminé';

    public function libelle(): string
    {
        return $this->value;
    }

    /** Classes Tailwind du badge affiché pour ce statut. */
    public function couleur(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'bg-amber-100 text-amber-800 ring-amber-600/20',
            self::VALIDE => 'bg-blue-100 text-blue-800 ring-blue-600/20',
            self::REFUSE => 'bg-red-100 text-red-800 ring-red-600/20',
            self::EN_COURS => 'bg-green-100 text-green-800 ring-green-600/20',
            self::TERMINE => 'bg-gray-100 text-gray-700 ring-gray-500/20',
        };
    }

    /** Le contenu (dates, questions) peut encore être modifié. */
    public function estModifiable(): bool
    {
        return in_array($this, [self::EN_ATTENTE, self::REFUSE, self::VALIDE], true);
    }

    /** Les résultats peuvent être consultés. */
    public function aDesResultats(): bool
    {
        return in_array($this, [self::EN_COURS, self::TERMINE], true);
    }
}
