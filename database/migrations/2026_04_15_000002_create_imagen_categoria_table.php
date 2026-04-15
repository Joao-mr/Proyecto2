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
        Schema::create('imagen_categoria', function (Blueprint $table) {
            $table->unsignedBigInteger('id_imagen');
            $table->unsignedBigInteger('id_categoria');

            $table->primary(['id_imagen', 'id_categoria']);

            $table->foreign('id_imagen')
                ->references('id')
                ->on('imagenes')
                ->onDelete('cascade');

            $table->foreign('id_categoria')
                ->references('id')
                ->on('categorias')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imagen_categoria');
    }
};
