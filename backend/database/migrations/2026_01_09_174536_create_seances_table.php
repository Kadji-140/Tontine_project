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
        Schema::create('seances', function (Blueprint $table) {
            $table->id();

            // Lien avec le cycle
            $table->foreignId('cycle_id')->constrained('cycles')->onDelete('cascade');

            $table->date('date_seance');

            // Statut de la séance (ouverte pour saisie, fermée quand finie)
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');

            // Total théorique calculé à la fermeture (cache)
            $table->decimal('total_encaisse', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seances');
    }
};
