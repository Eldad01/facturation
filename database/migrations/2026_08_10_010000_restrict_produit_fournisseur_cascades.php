<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
        });
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->foreign('produit_id')->references('id')->on('produits')->restrictOnDelete();
        });

        Schema::table('lignes_commande_achat', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
        });
        Schema::table('lignes_commande_achat', function (Blueprint $table) {
            $table->foreign('produit_id')->references('id')->on('produits')->restrictOnDelete();
        });

        Schema::table('commandes_achat', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
        });
        Schema::table('commandes_achat', function (Blueprint $table) {
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
        });
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->foreign('produit_id')->references('id')->on('produits')->cascadeOnDelete();
        });

        Schema::table('lignes_commande_achat', function (Blueprint $table) {
            $table->dropForeign(['produit_id']);
        });
        Schema::table('lignes_commande_achat', function (Blueprint $table) {
            $table->foreign('produit_id')->references('id')->on('produits')->cascadeOnDelete();
        });

        Schema::table('commandes_achat', function (Blueprint $table) {
            $table->dropForeign(['fournisseur_id']);
        });
        Schema::table('commandes_achat', function (Blueprint $table) {
            $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->cascadeOnDelete();
        });
    }
};
