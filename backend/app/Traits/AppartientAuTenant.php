<?php

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait AppartientAuTenant
{
    /**
     * Boot du trait pour injecter le scope global et renseigner automatiquement tenant_id.
     */
    public static function bootAppartientAuTenant(): void
    {
        // 1. Scope global automatique : filtre les enregistrements par le tenant actif
        static::addGlobalScope('tenant', function (Builder $builder) {
            // Si l'utilisateur connecté est un super-admin et qu'aucun tenant spécifique n'est ciblé,
            // on désactive le scope pour lui permettre la vue consolidée
            $user = auth()->user();
            if ($user && $user->est_super_admin && !app()->bound('tenant_actuel')) {
                return;
            }

            if (app()->bound('tenant_actuel')) {
                $tenant = app('tenant_actuel');
                if ($tenant && !empty($tenant->id)) {
                    $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenant->id);
                }
            }
        });

        // 2. À la création : auto-assignation du tenant actif
        static::creating(function ($model) {
            if (empty($model->tenant_id) && app()->bound('tenant_actuel')) {
                $tenant = app('tenant_actuel');
                if ($tenant && !empty($tenant->id)) {
                    $model->tenant_id = $tenant->id;
                }
            }
        });

        // 3. À la mise à jour : interdire le transfert frauduleux d'une entité vers un autre tenant
        static::updating(function ($model) {
            if ($model->isDirty('tenant_id') && $model->getOriginal('tenant_id') !== null) {
                // Seul le super_admin peut exceptionnellement réassigner un tenant
                $user = auth()->user();
                if (!$user || !$user->est_super_admin) {
                    throw new \RuntimeException("Sécurité Multi-Tenant : La modification de l'organisation (tenant_id) d'un enregistrement existant est strictement interdite.");
                }
            }
        });
    }

    /**
     * Relation vers l'organisation parente.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope local pour désactiver explicitement le filtre tenant (requêtes Super-Admin transversales).
     */
    public function scopeSansFiltreTenant(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
