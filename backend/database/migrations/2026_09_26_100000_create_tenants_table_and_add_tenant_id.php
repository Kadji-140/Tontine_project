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
        // 1. Création de la table des Tenants (Organisations / Tontines)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->unique();
            $table->string('statut')->default('actif'); // actif, suspendu
            $table->string('devise')->default('FCFA');
            $table->text('description')->nullable();
            $table->json('configuration')->nullable();
            $table->timestamps();
        });

        // 2. Ajout de tenant_id nullable sur les tables métier pour compatibilité totale
        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
            });
        }

        if (Schema::hasTable('cycles') && !Schema::hasColumn('cycles', 'tenant_id')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('seances') && !Schema::hasColumn('seances', 'tenant_id')) {
            Schema::table('seances', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('depenses') && !Schema::hasColumn('depenses', 'tenant_id')) {
            Schema::table('depenses', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('annonces') && !Schema::hasColumn('annonces', 'tenant_id')) {
            Schema::table('annonces', function (Blueprint $table) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('annonces') && Schema::hasColumn('annonces', 'tenant_id')) {
            Schema::table('annonces', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('depenses') && Schema::hasColumn('depenses', 'tenant_id')) {
            Schema::table('depenses', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('seances') && Schema::hasColumn('seances', 'tenant_id')) {
            Schema::table('seances', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('cycles') && Schema::hasColumn('cycles', 'tenant_id')) {
            Schema::table('cycles', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'tenant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            });
        }

        Schema::dropIfExists('tenants');
    }
};
