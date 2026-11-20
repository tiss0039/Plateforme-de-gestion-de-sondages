<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /** Types de question et libellés affichés. */
    public const TYPES = [
        'text' => 'Réponse libre',
        'multiple' => 'Choix unique',
        'checkbox' => 'Choix multiples',
    ];

    protected $fillable = [
        'sondage_id',
        'title',
        'question',
        'question_type',
    ];

    public function sondage(): BelongsTo
    {
        return $this->belongsTo(Sondage::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function reponses(): HasMany
    {
        return $this->hasMany(ParticipationReponse::class);
    }

    public function estAChoix(): bool
    {
        return in_array($this->question_type, ['multiple', 'checkbox'], true);
    }

    public function libelleType(): string
    {
        return self::TYPES[$this->question_type] ?? $this->question_type;
    }
}
