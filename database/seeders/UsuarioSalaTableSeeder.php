<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsuarioSalaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('usuario_sala')->delete();

        \DB::table('usuario_sala')->insert(array (
            0 =>
            array (
                'id_usuario' => 1,
                'id_sala' => 1,
                'fecha_entrada' => '2026-04-15 09:50:00',
            ),
            1 =>
            array (
                'id_usuario' => 2,
                'id_sala' => 1,
                'fecha_entrada' => '2026-04-15 09:55:00',
            ),
            2 =>
            array (
                'id_usuario' => 1,
                'id_sala' => 2,
                'fecha_entrada' => '2026-04-15 10:50:00',
            ),
            3 =>
            array (
                'id_usuario' => 3,
                'id_sala' => 2,
                'fecha_entrada' => '2026-04-15 10:55:00',
            ),
            4 =>
            array (
                'id_usuario' => 2,
                'id_sala' => 3,
                'fecha_entrada' => '2026-04-15 11:50:00',
            ),
        ));
    }
}
