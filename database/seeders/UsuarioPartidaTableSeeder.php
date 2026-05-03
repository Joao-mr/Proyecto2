<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UsuarioPartidaTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id', 'email')->all();
        $partidas = DB::table('partidas')->pluck('id', 'fecha_inicio')->all();

        $adminId = $users['admin@demo.com'] ?? null;
        $userId = $users['user@demo.com'] ?? null;
        $partida1Id = $partidas['2026-04-15 10:00:00'] ?? null;
        $partida2Id = $partidas['2026-04-15 11:00:00'] ?? null;
        $partida3Id = $partidas['2026-04-15 12:00:00'] ?? null;

        if (! is_int($adminId) || ! is_int($userId) || ! is_int($partida1Id) || ! is_int($partida2Id) || ! is_int($partida3Id)) {
            throw new RuntimeException('UsuarioPartidaTableSeeder: required users or partidas not found.');
        }

        $rows = [
            ['id_usuario' => $adminId, 'id_partida' => $partida1Id, 'puntuacion' => 850],
            ['id_usuario' => $userId, 'id_partida' => $partida1Id, 'puntuacion' => 720],
            ['id_usuario' => $adminId, 'id_partida' => $partida2Id, 'puntuacion' => 930],
            ['id_usuario' => $userId, 'id_partida' => $partida2Id, 'puntuacion' => 610],
            ['id_usuario' => $userId, 'id_partida' => $partida3Id, 'puntuacion' => 400],
        ];

        foreach ($rows as $row) {
            DB::table('usuario_partida')->updateOrInsert(
                ['id_usuario' => $row['id_usuario'], 'id_partida' => $row['id_partida']],
                ['puntuacion' => $row['puntuacion']]
            );
        }
    }
}
