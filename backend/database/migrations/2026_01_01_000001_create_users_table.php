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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('role')->default('membre'); // admin, tresorier, membre
            $table->boolean('est_super_admin')->default(false); // Super-Admin SaaS plateforme
            $table->string('status')->default('actif'); // actif, suspendu
            $table->boolean('is_active')->default(false); // Doit être validé par un administrateur du tenant
            $table->string('avatar')->nullable();
            $table->string('profession')->nullable();
            $table->string('adresse')->nullable();
            $table->string('cni')->nullable();
            $table->string('beneficiaire')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('role');
            $table->index('est_super_admin');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
