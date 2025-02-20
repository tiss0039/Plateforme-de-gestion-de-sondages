<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SondageAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sondage_id',
        'user_id',
        'role', 
    ];

    public function sondage()
    {
        return $this->belongsTo(Sondage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
