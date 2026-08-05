<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_commande_achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_achat_id')->constrained('commandes_achat')->onDelete('cascade');
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->integer('quantite');
            $table->integer('quantite_reçue')->default(0);
            $table->bigInteger('prix_unitaire')->comment('Prix unitaire négocié (XOF)');
            $table->bigInteger('montant')->comment('Montant total pour cette ligne (XOF)');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_commande_achat');
    }
};
