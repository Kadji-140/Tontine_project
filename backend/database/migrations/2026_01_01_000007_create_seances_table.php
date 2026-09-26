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
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('cycle_id')->constrained('cycles')->cascadeOnDelete();
            $table->date('date_seance');
            $table->string('statut')->default('ouverte'); // ouverte, fermee
            $table->decimal('total_encaisse', 12, 2)->default(0);
            $table->string('preuve_versement')->nullable();
            $table->string('etat_versement')->default('non_verse'); // non_verse, en_attente, valide, rejete
            $table->softDeletes();
            $table->timestamps();

            $table->index(['tenant_id', 'cycle_id']);
            $table->index(['tenant_id', 'statut']);
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
