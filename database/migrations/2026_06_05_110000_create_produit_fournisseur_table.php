<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produit_fournisseur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->onDelete('cascade');
            $table->bigInteger('prix_achat')->comment('Prix d\'achat négocié (XOF)');
            $table->integer('delai_livraison')->default(5)->comment('Délai en jours');
            $table->string('reference_fournisseur')->nullable()->comment('Référence produit chez le fournisseur');
            $table->integer('quantite_minimum')->default(1)->comment('Quantité minimale de commande');
            $table->boolean('prefere')->default(false)->comment('Fournisseur préféré');
            $table->timestamps();

            $table->unique(['produit_id', 'fournisseur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produit_fournisseur');
    }
};
