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
        Schema::create('usuario_sala', function (Blueprint $table) {
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_sala');
            $table->dateTime('fecha_entrada')->nullable();

            $table->primary(['id_usuario', 'id_sala']);

            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('id_sala')
                ->references('id')
                ->on('salas')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_sala');
    }
};
