<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('prets', function (Blueprint $table) {
            $table->boolean('date_echeance_modifiee')->default(false);
            $table->date('date_modification_proposee')->nullable();
        });
    }

    public function down()
    {
        Schema::table('prets', function (Blueprint $table) {
            $table->dropColumn(['date_echeance_modifiee', 'date_modification_proposee']);
        });
    }
};
