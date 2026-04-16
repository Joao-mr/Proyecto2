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
        Schema::create('usuario_partida', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_partida');
            $table->integer('puntuacion')->default(0);

            $table->primary(['id_usuario', 'id_partida']);

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('id_partida')
                ->references('id')
                ->on('partidas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_partida');
    }
};
