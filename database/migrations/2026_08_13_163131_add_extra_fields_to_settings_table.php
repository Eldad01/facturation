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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('boite_postale')->nullable();
            $table->string('regime_imposition')->nullable();
            $table->string('contact_nom')->nullable();
            $table->string('contact_telephone')->nullable();
            $table->string('banque_nom')->nullable();
            $table->string('banque_numero_compte')->nullable();
            $table->text('banque_autres_comptes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'boite_postale',
                'regime_imposition',
                'contact_nom',
                'contact_telephone',
                'banque_nom',
                'banque_numero_compte',
                'banque_autres_comptes',
            ]);
        });
    }
};
