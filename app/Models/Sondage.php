<?php

namespace App\Models;

use App\Enums\SondageStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sondage extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'date_debut',
        'date_fin',
        'statut',
        'user_id',
        'motif_refus',
        'moderateur_id',
        'modere_le',
    ];

    protected $casts = [
        'statut' => SondageStatus::class,
        'date_debut' => 'date',
        'date_fin' => 'date',
        'modere_le' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function moderateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderateur_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(SondageAssignment::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    /** Sondages en cours dont la période de réponse inclut aujourd'hui. */
    public function scopeOuvertsAuxReponses(Builder $query): Builder
    {
        return $query->where('statut', SondageStatus::EN_COURS)
            ->whereDate('date_debut', '<=', today())
            ->whereDate('date_fin', '>=', today());
    }

    public function estOuvertAuxReponses(): bool
    {
        return $this->statut === SondageStatus::EN_COURS
            && $this->date_debut->lte(today())
            && $this->date_fin->gte(today());
    }

    /**
     * Précision affichée à côté du statut « En cours » lorsque la période de
     * réponse n'inclut pas aujourd'hui.
     */
    public function precisionStatut(): ?string
    {
        if ($this->statut !== SondageStatus::EN_COURS) {
            return null;
        }

        if ($this->date_debut->gt(today())) {
            return 'ouverture le '.$this->date_debut->format('d/m/Y');
        }

        if ($this->date_fin->lt(today())) {
            return 'date de fin dépassée';
        }

        return null;
    }

    public function aRepondu(User $user): bool
    {
        return $this->participations()->where('user_id', $user->id)->exists();
    }
}
