<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sanction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seance_id',
        'montant',
        'motif',
        'est_reglee',
    ];

    protected $casts = [
        'est_reglee' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function seance()
    {
        return $this->belongsTo(Seance::class); // Peut être null si sanction hors séance
    }
}
