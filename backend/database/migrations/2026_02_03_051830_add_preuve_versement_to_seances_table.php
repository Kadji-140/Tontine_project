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
        Schema::table('seances', function (Blueprint $table) {
            $table->string('preuve_versement')->nullable()->after('total_encaisse');
            $table->string('etat_versement')->default('non_verse')->after('preuve_versement'); // non_verse, en_attente, valide, rejete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seances', function (Blueprint $table) {
            $table->dropColumn(['preuve_versement', 'etat_versement']);
        });
    }
};
