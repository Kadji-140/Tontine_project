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
            // Montant obligatoire "Mange-Mille" ou "Fonds de caisse" à chaque cotisation
            $table->decimal('montant_mange_mille', 10, 2)->default(0)->after('montant_part');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn('montant_mange_mille');
        });
    }
};
