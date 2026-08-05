<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('nom');
            $table->string('categorie')->nullable()->after('sku');
            $table->string('famille')->nullable()->after('categorie');
            $table->string('unite')->nullable()->after('famille');
            $table->text('description')->nullable()->after('unite');
            $table->string('photo')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('produits', function (Blueprint $table) {
            $table->dropColumn(['sku', 'categorie', 'famille', 'unite', 'description', 'photo']);
        });
    }
};
