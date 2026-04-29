<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SalaCategoriasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('sala_categorias')->delete();

        \DB::table('sala_categorias')->insert(array (
            0 =>
            array (
                'id_sala' => 1,
                'id_categoria' => 3,
            ),
            1 =>
            array (
                'id_sala' => 1,
                'id_categoria' => 4,
            ),
            2 =>
            array (
                'id_sala' => 2,
                'id_categoria' => 2,
            ),
            3 =>
            array (
                'id_sala' => 3,
                'id_categoria' => 5,
            ),
        ));
    }
}
