<?php

namespace App\Enums;

enum SondageStatus: string
{
    case EN_ATTENTE = 'En attente';
    case VALIDE = 'Validé';
    case REFUSE = 'Refusé';
    case EN_COURS = 'En cours';
    case TERMINE = 'Terminé';
}
