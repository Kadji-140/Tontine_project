<?php

namespace App\Models;

 use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory,Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
        'role',            // admin, tresorier, membre
        'est_super_admin', // Super-Admin SaaS Plateforme
        'status',          // actif, suspendu
        'avatar',
        'profession',
        'adresse',
        'cni',
        'beneficiaire',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'est_super_admin' => 'boolean',
    ];

    // --- RELATIONS (C'est ici la magie) ---

    // Un membre a plusieurs cotisations
    public function cotisations()
    {
        return $this->hasMany(Cotisation::class);
    }

    // Un membre peut avoir plusieurs prêts
    public function prets()
    {
        return $this->hasMany(Pret::class);
    }

    // Un membre peut avoir plusieurs sanctions
    public function sanctions()
    {
        return $this->hasMany(Sanction::class);
    }

    // Un membre participe à plusieurs cycles (avec son rang)
    public function cycles()
    {
        return $this->belongsToMany(Cycle::class, 'cycle_user')
                    ->withPivot('rang')
                    ->withTimestamps();
    }

    // Organisation / Tontine SaaS à laquelle appartient ce membre
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope local pour borner les requêtes aux membres de la tontine active
     * et exclure rigoureusement les comptes Super-Administrateur plateforme.
     */
    public function scopePourTenantActuel(Builder $query): Builder
    {
        $tenantId = null;
        if (app()->bound('tenant_actuel')) {
            $tenantId = app('tenant_actuel')?->id;
        } elseif (auth()->check()) {
            $tenantId = auth()->user()?->tenant_id;
        }

        return $query->where('tenant_id', $tenantId)
                     ->where('est_super_admin', false);
    }
}
