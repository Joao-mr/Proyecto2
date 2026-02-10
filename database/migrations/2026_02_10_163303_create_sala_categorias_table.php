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
        Schema::create('sala_categorias', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sala');
            $table->unsignedBigInteger('id_categoria');

            $table->primary(['id_sala', 'id_categoria']);

            $table->foreign('id_sala')
                ->references('id')
                ->on('salas')
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
        Schema::dropIfExists('sala_categorias');
    }
};
