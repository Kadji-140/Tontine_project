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
        Schema::create('prets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seance_id')->constrained('seances'); // Séance où le prêt est accordé

            $table->decimal('montant_demande', 12, 2);
            $table->decimal('interet_total', 10, 2); // Montant calculé des intérêts

            // Date limite de remboursement
            $table->date('date_echeance');

            // État du dossier
            $table->enum('statut', ['en_attente', 'valide', 'refuse', 'rembourse'])->default('en_attente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prets');
    }
};
