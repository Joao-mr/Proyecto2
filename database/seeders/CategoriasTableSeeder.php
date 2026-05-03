<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasTableSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nombre' => 'Deportes', 'descripcion' => 'Conoces a los mejores atletas del mundo. Adivina deportistas, equipos y momentos legendarios del deporte internacional.', 'imagen' => '/images/deportes.webp'],
            ['nombre' => 'Peliculas', 'descripcion' => 'Luces, camara, accion. Pon a prueba tu memoria con actores, directores y los titulos mas iconicos del septimo arte.', 'imagen' => '/images/pelicula.webp'],
            ['nombre' => 'Videojuegos', 'descripcion' => 'Desde los clasicos arcade hasta los ultimos lanzamientos. Demuestra que eres un gamer reconociendo personajes y sagas.', 'imagen' => '/images/videojuegos.webp'],
            ['nombre' => 'Geografia', 'descripcion' => 'Recorre el mundo sin moverte del sitio. Paises, capitales, banderas y curiosidades de todos los rincones del planeta.', 'imagen' => '/images/geografia.webp'],
        ];

        foreach ($categories as $category) {
            DB::table('categorias')->updateOrInsert(
                ['nombre' => $category['nombre']],
                [
                    'descripcion' => $category['descripcion'],
                    'imagen' => $category['imagen'],
                ]
            );
        }
    }
}
