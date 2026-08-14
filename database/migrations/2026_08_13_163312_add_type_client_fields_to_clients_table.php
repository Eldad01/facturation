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
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('type_client', ['particulier', 'entreprise'])->default('particulier')->after('prenom');
            $table->string('ifu')->nullable();
            $table->string('rccm')->nullable();
            $table->string('boite_postale')->nullable();
            $table->string('regime_imposition')->nullable();
            $table->string('contact_nom')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['type_client', 'ifu', 'rccm', 'boite_postale', 'regime_imposition', 'contact_nom']);
        });
    }
};
