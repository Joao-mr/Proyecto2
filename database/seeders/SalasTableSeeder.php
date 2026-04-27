<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('salas')->delete();

        DB::table('salas')->insert([
            [
                'id' => 1,
                'nombre' => 'Sala Marvel',
                'codigo' => 'MARVEL01',
                'id_creador' => 1,
                'tiempo_respuesta' => 30,
                'fecha_creacion' => '2026-04-15 10:00:00',
            ],
            [
                'id' => 2,
                'nombre' => 'Sala Deportes',
                'codigo' => 'DEPORT01',
                'id_creador' => 1,
                'tiempo_respuesta' => 20,
                'fecha_creacion' => '2026-04-15 10:00:00',
            ],
            [
                'id' => 3,
                'nombre' => 'Sala General',
                'codigo' => 'GENERAL01',
                'id_creador' => 2,
                'tiempo_respuesta' => 30,
                'fecha_creacion' => '2026-04-15 10:00:00',
            ],
        ]);
    }
}
