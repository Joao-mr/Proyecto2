<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PartidaImagenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('partida_imagen')->delete();

        \DB::table('partida_imagen')->insert(array (
            0 =>
            array (
                'id_partida' => 1,
                'id_imagen' => 4,
                'ronda' => 1,
            ),
            1 =>
            array (
                'id_partida' => 1,
                'id_imagen' => 5,
                'ronda' => 2,
            ),
            2 =>
            array (
                'id_partida' => 2,
                'id_imagen' => 3,
                'ronda' => 1,
            ),
            3 =>
            array (
                'id_partida' => 3,
                'id_imagen' => 1,
                'ronda' => 1,
            ),
            4 =>
            array (
                'id_partida' => 3,
                'id_imagen' => 2,
                'ronda' => 2,
            ),
        ));
    }
}
