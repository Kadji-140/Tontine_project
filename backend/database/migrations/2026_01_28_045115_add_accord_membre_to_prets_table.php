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
        Schema::table('prets', function (Blueprint $table) {
            // Par défaut true (car quand je crée ma demande, je suis d'accord avec moi-même)
            $table->boolean('est_accepte_par_membre')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('prets', function (Blueprint $table) {
            $table->dropColumn('est_accepte_par_membre');
        });
    }
};
