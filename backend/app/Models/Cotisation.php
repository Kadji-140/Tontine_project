<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotisation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seance_id',
        'montant',
        'enregistre_par',
        'type', // tontine, secours
    ];

    // La cotisation appartient à un Membre
    public function user()
    {
        return $this->belongsTo(User::class);
    }
// Relation pour savoir QUI a saisi l'opération
    public function auteur()
    {
        return $this->belongsTo(User::class, 'enregistre_par');
    }
    // La cotisation a été faite lors d'une Séance
    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
}
