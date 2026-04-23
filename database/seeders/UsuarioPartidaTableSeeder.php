<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsuarioPartidaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('usuario_partida')->delete();

        \DB::table('usuario_partida')->insert(array (
            0 =>
            array (
                'id_usuario' => 1,
                'id_partida' => 1,
                'puntuacion' => 850,
            ),
            1 =>
            array (
                'id_usuario' => 2,
                'id_partida' => 1,
                'puntuacion' => 720,
            ),
            2 =>
            array (
                'id_usuario' => 1,
                'id_partida' => 2,
                'puntuacion' => 930,
            ),
            3 =>
            array (
                'id_usuario' => 3,
                'id_partida' => 2,
                'puntuacion' => 610,
            ),
            4 =>
            array (
                'id_usuario' => 2,
                'id_partida' => 3,
                'puntuacion' => 400,
            ),
        ));
    }
}
