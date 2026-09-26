<?php

namespace App\Models;

 use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
