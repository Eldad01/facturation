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
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->enum('statut', ['brouillon', 'validee'])->default('brouillon');
            $table->foreignId('user_id_creation')->constrained('users');
            $table->foreignId('user_id_validation')->nullable()->constrained('users');
            $table->timestamp('date_validation')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaires');
    }
};
