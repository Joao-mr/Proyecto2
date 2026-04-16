<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PartidasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('partidas')->delete();

        \DB::table('partidas')->insert(array (
            0 =>
            array (
                'id' => 1,
                'id_sala' => 1,
                'fecha_inicio' => '2026-04-15 10:00:00',
                'fecha_fin' => '2026-04-15 10:15:00',
            ),
            1 =>
            array (
                'id' => 2,
                'id_sala' => 2,
                'fecha_inicio' => '2026-04-15 11:00:00',
                'fecha_fin' => '2026-04-15 11:10:00',
            ),
            2 =>
            array (
                'id' => 3,
                'id_sala' => 3,
                'fecha_inicio' => '2026-04-15 12:00:00',
                'fecha_fin' => null,
            ),
        ));
    }
}
