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
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seance_id')->nullable()->constrained('seances');

            $table->decimal('montant', 10, 2);
            $table->string('motif'); // Ex: "Retard", "Absence", "Bruit"

            $table->boolean('est_reglee')->default(false); // Est-ce qu'il a payé l'amende ?

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
