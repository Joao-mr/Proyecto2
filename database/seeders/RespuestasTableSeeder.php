<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class RespuestasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('respuestas')->delete();

        \DB::table('respuestas')->insert(array (
            0 =>
            array (
                'id' => 1,
                'id_usuario' => 1,
                'id_imagen' => 4,
                'respuesta' => 'spiderman',
                'es_correcta' => true,
                'tiempo' => 8,
                'created_at' => '2026-04-15 10:01:00',
                'updated_at' => '2026-04-15 10:01:00',
            ),
            1 =>
            array (
                'id' => 2,
                'id_usuario' => 2,
                'id_imagen' => 4,
                'respuesta' => 'batman',
                'es_correcta' => false,
                'tiempo' => 12,
                'created_at' => '2026-04-15 10:01:30',
                'updated_at' => '2026-04-15 10:01:30',
            ),
            2 =>
            array (
                'id' => 3,
                'id_usuario' => 1,
                'id_imagen' => 5,
                'respuesta' => 'minecraft',
                'es_correcta' => true,
                'tiempo' => 5,
                'created_at' => '2026-04-15 10:05:00',
                'updated_at' => '2026-04-15 10:05:00',
            ),
            3 =>
            array (
                'id' => 4,
                'id_usuario' => 1,
                'id_imagen' => 3,
                'respuesta' => 'futbol',
                'es_correcta' => true,
                'tiempo' => 6,
                'created_at' => '2026-04-15 11:01:00',
                'updated_at' => '2026-04-15 11:01:00',
            ),
            4 =>
            array (
                'id' => 5,
                'id_usuario' => 3,
                'id_imagen' => 3,
                'respuesta' => 'baloncesto',
                'es_correcta' => false,
                'tiempo' => 15,
                'created_at' => '2026-04-15 11:02:00',
                'updated_at' => '2026-04-15 11:02:00',
            ),
        ));
    }
}
