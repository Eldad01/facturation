<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes_achat', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->onDelete('cascade');
            $table->enum('statut', ['brouillon', 'confirmee', 'reçue_partiellement', 'reçue', 'annulee'])->default('brouillon');
            $table->date('date_commande');
            $table->date('date_reception_prevue')->nullable();
            $table->date('date_reception')->nullable();
            $table->bigInteger('montant_total')->default(0)->comment('Montant total HT (XOF)');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes_achat');
    }
};
