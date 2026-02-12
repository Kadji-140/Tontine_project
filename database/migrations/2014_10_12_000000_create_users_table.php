<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom complet
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique(); // Téléphone (Important pour le Cameroun)
            $table->string('password');

            // Gestion des rôles (admin, tresorier, membre)
            $table->enum('role', ['admin', 'tresorier', 'membre'])->default('membre');

            // Statut (actif, suspendu, exclu)
            $table->enum('status', ['actif', 'suspendu'])->default('actif');

            // Photo de profil (chemin du fichier)
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->rememberToken();
            $table->timestamps(); // created_at, updated_at
            $table->softDeletes(); // Pour ne pas supprimer définitivement un membre (Page 20)
        });
    }
};
