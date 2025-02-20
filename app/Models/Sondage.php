<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\SondageStatus;

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
    ];

   
    protected $casts = [
        'statut' => SondageStatus::class, 
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function assignments()
    {
        return $this->hasMany(SondageAssignment::class);
    }

    public function responses()
    {
        return $this->hasMany(Reponse::class);
    }
}
