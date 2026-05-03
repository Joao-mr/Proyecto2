<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UsuarioSalaTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id', 'email')->all();
        $salas = DB::table('salas')->pluck('id', 'codigo')->all();

        $adminId = $users['admin@demo.com'] ?? null;
        $userId = $users['user@demo.com'] ?? null;
        $marvelSalaId = $salas['MARVEL01'] ?? null;
        $deportesSalaId = $salas['DEPORT01'] ?? null;
        $generalSalaId = $salas['GENERAL01'] ?? null;

        if (! is_int($adminId) || ! is_int($userId) || ! is_int($marvelSalaId) || ! is_int($deportesSalaId) || ! is_int($generalSalaId)) {
            throw new RuntimeException('UsuarioSalaTableSeeder: required users or salas not found.');
        }

        $rows = [
            ['id_usuario' => $adminId, 'id_sala' => $marvelSalaId, 'fecha_entrada' => '2026-04-15 09:50:00'],
            ['id_usuario' => $userId, 'id_sala' => $marvelSalaId, 'fecha_entrada' => '2026-04-15 09:55:00'],
            ['id_usuario' => $adminId, 'id_sala' => $deportesSalaId, 'fecha_entrada' => '2026-04-15 10:50:00'],
            ['id_usuario' => $userId, 'id_sala' => $deportesSalaId, 'fecha_entrada' => '2026-04-15 10:55:00'],
            ['id_usuario' => $userId, 'id_sala' => $generalSalaId, 'fecha_entrada' => '2026-04-15 11:50:00'],
        ];

        foreach ($rows as $row) {
            DB::table('usuario_sala')->updateOrInsert(
                ['id_usuario' => $row['id_usuario'], 'id_sala' => $row['id_sala']],
                ['fecha_entrada' => $row['fecha_entrada']]
            );
        }
    }
}
