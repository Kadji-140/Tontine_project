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
            $table->string('nom'); // Ex: "Cycle 2025-2026"
            $table->date('date_debut');
            $table->date('date_fin');

            // Paramètres financiers globaux du cycle
            $table->decimal('montant_part', 10, 2)->default(10000); // Ex: 10 000 FCFA la part
            $table->decimal('taux_interet', 5, 2)->default(10); // Ex: 10% d'intérêt

            $table->boolean('est_actif')->default(true); // Pour savoir quel est le cycle en cours
            $table->timestamps();
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
