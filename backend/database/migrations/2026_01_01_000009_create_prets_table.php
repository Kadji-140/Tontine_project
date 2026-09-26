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
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('montant_demande', 12, 2);
            $table->decimal('interet_total', 12, 2)->default(0);
            $table->date('date_echeance');
            $table->string('statut')->default('en_attente'); // en_attente, valide, refuse, rembourse
            $table->date('date_echeance_modifiee')->nullable();
            $table->date('date_modification_proposee')->nullable();
            $table->boolean('est_accepte_par_membre')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'statut']);
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
