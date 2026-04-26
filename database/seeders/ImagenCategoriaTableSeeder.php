<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ImagenCategoriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('imagen_categoria')->delete();

        \DB::table('imagen_categoria')->insert(array (
            0 =>
            array (
                'id_imagen' => 1,
                'id_categoria' => 1,
            ),
            1 =>
            array (
                'id_imagen' => 2,
                'id_categoria' => 1,
            ),
            2 =>
            array (
                'id_imagen' => 3,
                'id_categoria' => 2,
            ),
            3 =>
            array (
                'id_imagen' => 4,
                'id_categoria' => 3,
            ),
            4 =>
            array (
                'id_imagen' => 5,
                'id_categoria' => 4,
            ),
        ));
    }
}
