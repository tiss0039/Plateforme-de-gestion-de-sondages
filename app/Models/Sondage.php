<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sondage extends Model
{
    //
    use HasFactory;

    protected $fillable = ['titre', 'description', 'statut', 'cree_par'];


    public function client() : BelongsTo {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function questions():HasMany {
        return $this->hasMany(Question::class);
    }

    public function responses(): HasMany {
        return $this->hasMnay(Responses::class);
    }

    public function sondageAssignees() {
        return $this->belongsToMany(User::class, 'sondage_assignments', 'sondage_id', 'sondeur_id');
    }
}
