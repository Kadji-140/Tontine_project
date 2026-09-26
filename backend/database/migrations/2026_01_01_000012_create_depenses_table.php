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
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('seance_id')->constrained('seances')->cascadeOnDelete();
            $table->foreignId('enregistre_par')->nullable()->constrained('users')->nullOnDelete();
            $table->string('motif');
            $table->decimal('montant', 12, 2);
            $table->string('statut')->default('en_attente'); // en_attente, validee, rejetee
            $table->string('justificatif')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'seance_id']);
            $table->index(['tenant_id', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depenses');
    }
};
