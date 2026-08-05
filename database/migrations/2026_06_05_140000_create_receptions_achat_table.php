<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receptions_achat', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->foreignId('commande_achat_id')->constrained('commandes_achat')->onDelete('cascade');
            $table->date('date_reception');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receptions_achat');
    }
};
