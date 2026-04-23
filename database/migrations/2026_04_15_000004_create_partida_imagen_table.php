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
        Schema::create('partida_imagen', function (Blueprint $table) {
            $table->unsignedBigInteger('id_partida');
            $table->unsignedBigInteger('id_imagen');
            $table->integer('ronda');

            $table->primary(['id_partida', 'id_imagen']);

            $table->foreign('id_partida')
                ->references('id')
                ->on('partidas')
                ->onDelete('cascade');

            $table->foreign('id_imagen')
                ->references('id')
                ->on('imagenes')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partida_imagen');
    }
};
