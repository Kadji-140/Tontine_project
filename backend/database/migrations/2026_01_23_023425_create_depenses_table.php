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
        Schema::create('depenses', function (Blueprint $table) {
            $table->id();

            // La dépense est liée à une séance (c'est là qu'on sort l'argent)
            $table->foreignId('seance_id')->constrained('seances')->onDelete('cascade');

            // Qui a enregistré la dépense ? (Trésorier)
            $table->foreignId('enregistre_par')->constrained('users');

            $table->string('motif'); // Ex: "Achat boisson", "Transport", "Stylos"
            $table->decimal('montant', 10, 2); // Ex: 5000.00

            $table->timestamps();
        });
    }
};
