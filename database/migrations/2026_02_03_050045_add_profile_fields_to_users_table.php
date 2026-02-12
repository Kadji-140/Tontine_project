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
        Schema::table('users', function (Blueprint $table) {
            $table->string('profession')->nullable()->after('phone');
            $table->string('adresse')->nullable()->after('profession');
            $table->string('cni')->nullable()->unique()->after('adresse'); // Numéro CNI unique
            $table->string('beneficiaire')->nullable()->after('cni'); // Nom du bénéficiaire en cas de décès
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profession', 'adresse', 'cni', 'beneficiaire']);
        });
    }
};
