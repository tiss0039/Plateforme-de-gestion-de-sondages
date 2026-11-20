<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Réponse complète d'un utilisateur à un sondage. */
class Participation extends Model
{
    protected $fillable = [
        'sondage_id',
        'user_id',
    ];

    public function sondage(): BelongsTo
    {
        return $this->belongsTo(Sondage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(ParticipationReponse::class);
    }
}
