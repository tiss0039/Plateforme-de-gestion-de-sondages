<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Reponse extends Model
{
    //
    use HasFactory;

    protected $fillable = ['user_id', 'sondage_id'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function sondage(): BelongsTo {
        return $this->belongsTo(Sondage::class);
    }

    public function stockage_Reponses(): HasMany {
        return $this->hasMany(Stockage_Reponse::class);
    }

}
