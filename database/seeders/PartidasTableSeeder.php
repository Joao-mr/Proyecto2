<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartidasTableSeeder extends Seeder
{
    public function run(): void
    {
        $salas = DB::table('salas')->pluck('id', 'codigo')->all();
        $marvelSalaId = $salas['MARVEL01'] ?? null;
        $deportesSalaId = $salas['DEPORT01'] ?? null;
        $generalSalaId = $salas['GENERAL01'] ?? null;

        if (! is_int($marvelSalaId) || ! is_int($deportesSalaId) || ! is_int($generalSalaId)) {
            throw new RuntimeException('PartidasTableSeeder: required salas not found.');
        }

        $partidas = [
            ['id_sala' => $marvelSalaId, 'fecha_inicio' => '2026-04-15 10:00:00', 'fecha_fin' => '2026-04-15 10:15:00'],
            ['id_sala' => $deportesSalaId, 'fecha_inicio' => '2026-04-15 11:00:00', 'fecha_fin' => '2026-04-15 11:10:00'],
            ['id_sala' => $generalSalaId, 'fecha_inicio' => '2026-04-15 12:00:00', 'fecha_fin' => null],
        ];

        foreach ($partidas as $partida) {
            DB::table('partidas')->updateOrInsert(
                ['id_sala' => $partida['id_sala'], 'fecha_inicio' => $partida['fecha_inicio']],
                ['fecha_fin' => $partida['fecha_fin']]
            );
        }
    }
}
