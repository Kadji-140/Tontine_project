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
        Schema::create('remboursements', function (Blueprint $table) {
            $table->id();

            // On lie le remboursement à un Prêt spécifique
            $table->foreignId('pret_id')->constrained('prets')->onDelete('cascade');

            // Le remboursement se fait lors d'une séance (pour encaisser l'argent)
            $table->foreignId('seance_id')->constrained('seances');

            // Qui paie ? (Normalement le même que l'emprunteur, mais on stocke pour l'historique)
            $table->foreignId('user_id')->constrained('users');

            // Qui a saisi ? (Trésorier)
            $table->foreignId('enregistre_par')->constrained('users');

            $table->decimal('montant', 12, 2);

            $table->timestamps();
        });
    }
};
