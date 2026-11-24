<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change DECIMAL columns to BIGINT for whole numbers (XOF currency)
     */
    public function up(): void
    {
        // Update lignes_facture table - change to BIGINT
        Schema::table('lignes_facture', function (Blueprint $table) {
            $table->bigInteger('prix_unitaire')->change();
            $table->bigInteger('total_ligne')->change();
        });

        // Update factures table
        Schema::table('factures', function (Blueprint $table) {
            $table->bigInteger('total')->default(0)->change();
        });

        // Update produits table
        Schema::table('produits', function (Blueprint $table) {
            $table->bigInteger('prix_achat')->nullable()->change();
            $table->bigInteger('prix_vente')->change();
            $table->bigInteger('stock')->default(0)->change();
            $table->bigInteger('seuil_alerte')->default(5)->change();
        });

        // Update mouvements_stock table
        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->bigInteger('quantite')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to DECIMAL(10,2)
        Schema::table('lignes_facture', function (Blueprint $table) {
            $table->decimal('prix_unitaire', 10, 2)->change();
            $table->decimal('total_ligne', 10, 2)->change();
        });

        Schema::table('factures', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->default(0)->change();
        });

        Schema::table('produits', function (Blueprint $table) {
            $table->decimal('prix_achat', 10, 2)->nullable()->change();
            $table->decimal('prix_vente', 10, 2)->change();
            $table->integer('stock')->default(0)->change();
            $table->integer('seuil_alerte')->default(5)->change();
        });

        Schema::table('mouvements_stock', function (Blueprint $table) {
            $table->integer('quantite')->change();
        });
    }
};
