<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategoriasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('categorias')->delete();

        \DB::table('categorias')->insert(array (
            0 =>
            array (
                'id' => 1,
                'nombre' => 'Animales',
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ),
            1 =>
            array (
                'id' => 2,
                'nombre' => 'Deportes',
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ),
            2 =>
            array (
                'id' => 3,
                'nombre' => 'Películas',
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ),
            3 =>
            array (
                'id' => 4,
                'nombre' => 'Videojuegos',
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ),
            4 =>
            array (
                'id' => 5,
                'nombre' => 'Geografía',
                'created_at' => '2026-04-15 00:00:00',
                'updated_at' => '2026-04-15 00:00:00',
            ),
        ));
    }
}
