<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    // Dans app/Models/Depense.php
    protected $fillable = [
        'seance_id', 'enregistre_par', 'motif', 'montant', 'statut'
    ];

    public function seance() {
        return $this->belongsTo(Seance::class);
    }
    public function auteur() {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
}
