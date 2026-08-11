<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paiement_id')->constrained('paiements')->cascadeOnDelete();
            $table->foreignId('ligne_facture_id')->constrained('lignes_facture')->cascadeOnDelete();
            $table->bigInteger('quantite');
            $table->bigInteger('montant');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_paiements');
    }
};
