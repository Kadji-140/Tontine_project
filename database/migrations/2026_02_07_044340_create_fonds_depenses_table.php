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
        Schema::create('fonds_depenses', function (Blueprint $table) {
            $table->id();
            
            // Lier au cycle (obligatoire)
            $table->foreignId('cycle_id')->constrained('cycles')->onDelete('cascade');
            
            // Lier à une séance (s'il y a lieu, ex: lors d'une réunion)
            $table->foreignId('seance_id')->nullable()->constrained('seances')->onDelete('set null');
            
            // Source du fonds (ex: membre qui paie une sanction ou cotise)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Type de recette/dépense
            // Types prévus: 'sanction', 'inscription', 'secours', 'mange_mille', 'autre'
            $table->string('type'); 
            
            $table->decimal('montant', 12, 2);
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fonds_depenses');
    }
};
