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
        Schema::table('factures', function (Blueprint $table) {
            $table->string('status')->default('pro-forma');
            $table->date('date_echeance')->nullable();
            $table->decimal('montant_paye', 10, 2)->default(0);
            $table->date('date_paiement')->nullable();
        });

        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->string('mode')->default('especes');
            $table->string('reference')->nullable();
            $table->string('note')->nullable();
            $table->date('date_paiement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');

        Schema::table('factures', function (Blueprint $table) {
            $table->dropColumn(['status', 'date_echeance', 'montant_paye', 'date_paiement']);
        });
    }
};
