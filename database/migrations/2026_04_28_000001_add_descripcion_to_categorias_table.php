<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('nombre');
        });

        $descriptions = [
            'Animales'     => 'Desde mascotas hasta especies salvajes. ¿Cuánto sabes sobre el reino animal? Descúbrelo reconociendo fotos de criaturas de todo el planeta.',
            'Deportes'     => '¿Conoces a los mejores atletas del mundo? Adivina deportistas, equipos y momentos legendarios del deporte internacional.',
            'Peliculas'    => 'Luces, cámara, acción. Pon a prueba tu memoria con actores, directores y los títulos más icónicos del séptimo arte.',
            'Videojuegos'  => 'Desde los clásicos arcade hasta los últimos lanzamientos. Demuestra que eres un auténtico gamer reconociendo personajes y sagas.',
            'Geografia'    => 'Recorre el mundo sin moverte del sitio. Países, capitales, banderas y curiosidades de todos los rincones del planeta.',
        ];

        foreach ($descriptions as $nombre => $descripcion) {
            DB::table('categorias')
                ->where('nombre', $nombre)
                ->whereNull('descripcion')
                ->update(['descripcion' => $descripcion]);
        }
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
