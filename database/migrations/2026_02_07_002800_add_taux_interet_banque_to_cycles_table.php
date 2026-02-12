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
        Schema::table('cycles', function (Blueprint $table) {
            // Ajouter le taux d'intérêt bancaire (séparé du taux de prêt)
            $table->decimal('taux_interet_banque', 5, 2)->default(5.0)->after('taux_interet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn('taux_interet_banque');
        });
    }
};
