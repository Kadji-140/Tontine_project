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
        Schema::create('cotisations', function (Blueprint $table) {
            $table->id();

            // Clés étrangères
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seance_id')->constrained('seances')->onDelete('cascade');

            // Détails financiers
            $table->decimal('montant', 10, 2); // Utilisation de Decimal pour l'argent (pas de Float !)

            // Type : 'tontine' (fond caisse) ou 'secours' (fond social)
            $table->enum('type', ['tontine', 'secours'])->default('tontine');

            $table->timestamps(); // Sert aussi de date de saisie
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotisations');
    }
};
