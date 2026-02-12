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
        // On convertit le champ 'type' en STRING simple pour éviter les problèmes de ENUM/CHECK Constraints
        // C'est compatible MySQL et SQLite sans prise de tête
        
        // 1. Créer table temp
        Schema::create('cotisations_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seance_id')->constrained('seances')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('type')->default('tontine'); // <--- CHANGEMENT ICI (String au lieu d'Enum)
            $table->timestamps();
            $table->foreignId('enregistre_par')->nullable()->constrained('users');
        });

        // 2. Copier les données
        DB::statement('INSERT INTO cotisations_temp (id, user_id, seance_id, montant, type, created_at, updated_at, enregistre_par) SELECT id, user_id, seance_id, montant, type, created_at, updated_at, enregistre_par FROM cotisations');

        // 3. Drop & Rename
        Schema::drop('cotisations');
        Schema::rename('cotisations_temp', 'cotisations');
    }

    public function down(): void
    {
        // En cas de rollback, on remet un ENUM (si vraiment nécessaire)
        Schema::create('cotisations_temp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seance_id')->constrained('seances')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->enum('type', ['tontine', 'secours', 'banque'])->default('tontine');
            $table->timestamps();
            $table->foreignId('enregistre_par')->nullable()->constrained('users');
        });

        DB::statement('INSERT INTO cotisations_temp (id, user_id, seance_id, montant, type, created_at, updated_at, enregistre_par) SELECT id, user_id, seance_id, montant, type, created_at, updated_at, enregistre_par FROM cotisations');

        Schema::drop('cotisations');
        Schema::rename('cotisations_temp', 'cotisations');
    }
};
