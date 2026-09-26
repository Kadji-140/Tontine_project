<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = [
        'nom',
        'slug',
        'statut',
        'devise',
        'description',
        'configuration',
    ];

    protected $casts = [
        'configuration' => 'array',
    ];

    /**
     * Membres et administrateurs associés à cette organisation/tontine.
     */
    public function utilisateurs(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Cycles de tontine organisés par ce tenant.
     */
    public function cycles(): HasMany
    {
        return $this->hasMany(Cycle::class, 'tenant_id');
    }

    /**
     * Séances organisées au sein de ce tenant.
     */
    public function seances(): HasMany
    {
        return $this->hasMany(Seance::class, 'tenant_id');
    }

    /**
     * Dépenses gérées par ce tenant.
     */
    public function depenses(): HasMany
    {
        return $this->hasMany(Depense::class, 'tenant_id');
    }

    /**
     * Annonces diffusées par ce tenant.
     */
    public function annonces(): HasMany
    {
        return $this->hasMany(Annonce::class, 'tenant_id');
    }
}
