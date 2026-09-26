<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('nom');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->decimal('montant_part', 12, 2);
            $table->decimal('montant_mange_mille', 12, 2)->default(0);
            $table->decimal('taux_interet', 5, 2)->default(0); // Taux Prêt (%)
            $table->decimal('taux_interet_banque', 5, 2)->default(0); // Taux Banque (%)
            $table->boolean('est_actif')->default(true);
            $table->string('frequence_paiement')->default('mensuelle'); // hebdomadaire, bimensuelle, mensuelle
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'id']);
            $table->index(['tenant_id', 'est_actif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
