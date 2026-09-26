<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    use HasFactory;

    protected $fillable = [
        'cycle_id',
        'date_seance',
        'statut',        // ouverte, fermee
        'total_encaisse',
        'preuve_versement',
        'etat_versement', // non_verse, en_attente, valide, rejete
    ];

    protected $casts = [
        'date_seance' => 'date',
    ];

    // Une séance appartient à un cycle
    public function cycle()
    {
        return $this->belongsTo(Cycle::class);
    }

    // Une séance a plusieurs cotisations reçues ce jour-là
    public function cotisations()
    {
        return $this->hasMany(Cotisation::class);
    }

    // Une séance peut avoir des prêts accordés ce jour-là
    public function prets()
    {
        return $this->hasMany(Pret::class);
    }
    // Dans Seance.php
    public function remboursements()
    {
        return $this->hasMany(Remboursement::class);
    }
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    // Dans app/Models/Seance.php

    public function depenses()
    {
        return $this->hasMany(Depense::class);
    }

    public function fonds_depenses()
    {
        return $this->hasMany(FondsDepense::class);
    }
}
