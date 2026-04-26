<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SalasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('salas')->delete();

        \DB::table('salas')->insert(array (
            0 =>
            array (
                'id' => 1,
                'nombre' => 'Sala Marvel',
                'codigo' => 'MARVEL01',
                'id_creador' => 1,
                'tiempo_respuesta' => 30,
                'fecha_creacion' => '2026-04-15 10:00:00',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            1 =>
            array (
                'id' => 2,
                'nombre' => 'Sala Deportes',
                'codigo' => 'DEPORT01',
                'id_creador' => 1,
                'tiempo_respuesta' => 20,
                'fecha_creacion' => '2026-04-15 10:00:00',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            2 =>
            array (
                'id' => 3,
                'nombre' => 'Sala General',
                'codigo' => 'GENERAL01',
                'id_creador' => 2,
                'tiempo_respuesta' => 30,
                'fecha_creacion' => '2026-04-15 10:00:00',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
        ));
    }
}
