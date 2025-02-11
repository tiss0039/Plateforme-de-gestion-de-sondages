<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Question extends Model
{
    //
    use HasFactory;

    protected $fillable = ['sondage_id', 'question_texte', 'type'];

    public function sondage(): BelongsTo {
        return $this->belongsTo(Sondage::class);
    }

    public function options(): HasMany {
        return $this->hasMany(Option::class);
    }

    public function stockage_Reponses(): HasMany {
        return $this->hasMany(StockageReponse::class);
    }
}
