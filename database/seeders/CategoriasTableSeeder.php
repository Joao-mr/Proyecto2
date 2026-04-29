<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('sala_categorias')->whereIn('id_categoria', [1])->delete();
        DB::table('imagen_categoria')->whereIn('id_categoria', [1])->delete();
        DB::table('categorias')->delete();

        DB::table('categorias')->insert([
            ['id' => 2, 'nombre' => 'Deportes',    'descripcion' => '¿Conoces a los mejores atletas del mundo? Adivina deportistas, equipos y momentos legendarios del deporte internacional.',  'imagen' => '/images/deportes.webp'],
            ['id' => 3, 'nombre' => 'Peliculas',   'descripcion' => 'Luces, cámara, acción. Pon a prueba tu memoria con actores, directores y los títulos más icónicos del séptimo arte.',         'imagen' => '/images/pelicula.webp'],
            ['id' => 4, 'nombre' => 'Videojuegos', 'descripcion' => 'Desde los clásicos arcade hasta los últimos lanzamientos. Demuestra que eres un auténtico gamer reconociendo personajes y sagas.', 'imagen' => '/images/videojuegos.webp'],
            ['id' => 5, 'nombre' => 'Geografia',   'descripcion' => 'Recorre el mundo sin moverte del sitio. Países, capitales, banderas y curiosidades de todos los rincones del planeta.',       'imagen' => '/images/geografia.webp'],
        ]);
    }
}
