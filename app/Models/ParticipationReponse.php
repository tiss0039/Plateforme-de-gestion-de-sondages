<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Réponse à une question : texte libre, option choisie (choix unique) ou
 * option cochée (choix multiples, une ligne par case cochée).
 */
class ParticipationReponse extends Model
{
    protected $table = 'participation_reponses';

    protected $fillable = [
        'participation_id',
        'question_id',
        'option_id',
        'texte',
    ];

    public function participation(): BelongsTo
    {
        return $this->belongsTo(Participation::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
