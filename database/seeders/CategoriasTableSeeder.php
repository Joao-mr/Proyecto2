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
        DB::table('categorias')->delete();

        DB::table('categorias')->insert([
            ['id' => 1, 'nombre' => 'Animales'],
            ['id' => 2, 'nombre' => 'Deportes'],
            ['id' => 3, 'nombre' => 'Peliculas'],
            ['id' => 4, 'nombre' => 'Videojuegos'],
            ['id' => 5, 'nombre' => 'Geografia'],
        ]);
    }
}
