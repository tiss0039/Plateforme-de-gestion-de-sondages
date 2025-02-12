<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Stockage_Reponse extends Model
{
    //
    use HasFactory;
    protected $fillable = ['reponse_id', 'question_id', 'option_id', 'texte_reponse'];
    
    public function response(): BelongsTo {
        return $this->belongsTo(Response::class);
    }

    public function question(): BelongsTo {
        return $this->belongsTo(Question::class);
    }

    public function option(): BelongsTo {
        return $this->belongsTo(Option::class);
    }
}
