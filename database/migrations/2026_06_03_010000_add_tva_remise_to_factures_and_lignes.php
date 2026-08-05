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
            if (!Schema::hasColumn('factures', 'remise')) {
                $table->decimal('remise', 10, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('factures', 'tva')) {
                $table->decimal('tva', 5, 2)->default(0)->after('remise');
            }
        });

        Schema::table('lignes_facture', function (Blueprint $table) {
            if (!Schema::hasColumn('lignes_facture', 'remise')) {
                $table->decimal('remise', 10, 2)->default(0)->after('total_ligne');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lignes_facture', function (Blueprint $table) {
            if (Schema::hasColumn('lignes_facture', 'remise')) {
                $table->dropColumn('remise');
            }
        });

        Schema::table('factures', function (Blueprint $table) {
            if (Schema::hasColumn('factures', 'tva')) {
                $table->dropColumn('tva');
            }
            if (Schema::hasColumn('factures', 'remise')) {
                $table->dropColumn('remise');
            }
        });
    }
};
