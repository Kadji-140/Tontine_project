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
        Schema::table('cotisations', function (Blueprint $table) {
            // On ajoute la colonne qui pointe vers la table users
            // nullable() car les anciennes cotisations n'ont pas d'auteur (si tu en as déjà créées)
            $table->foreignId('enregistre_par')->nullable()->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('cotisations', function (Blueprint $table) {
            $table->dropForeign(['enregistre_par']);
            $table->dropColumn('enregistre_par');
        });
    }
};
