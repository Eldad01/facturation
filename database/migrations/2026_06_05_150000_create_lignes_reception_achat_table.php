<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_reception_achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reception_achat_id')->constrained('receptions_achat')->onDelete('cascade');
            $table->foreignId('ligne_commande_achat_id')->constrained('lignes_commande_achat')->onDelete('cascade');
            $table->integer('quantite_reçue');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_reception_achat');
    }
};
