<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalasTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id', 'email')->all();
        $adminId = $users['admin@demo.com'] ?? null;
        $userId = $users['user@demo.com'] ?? null;

        if (! is_int($adminId) || ! is_int($userId)) {
            throw new RuntimeException('SalasTableSeeder: bootstrap users not found.');
        }

        $salas = [
            ['codigo' => 'MARVEL01', 'nombre' => 'Sala Marvel', 'id_creador' => $adminId, 'tiempo_respuesta' => 30, 'fecha_creacion' => '2026-04-15 10:00:00'],
            ['codigo' => 'DEPORT01', 'nombre' => 'Sala Deportes', 'id_creador' => $adminId, 'tiempo_respuesta' => 20, 'fecha_creacion' => '2026-04-15 10:00:00'],
            ['codigo' => 'GENERAL01', 'nombre' => 'Sala General', 'id_creador' => $userId, 'tiempo_respuesta' => 30, 'fecha_creacion' => '2026-04-15 10:00:00'],
        ];

        foreach ($salas as $sala) {
            DB::table('salas')->updateOrInsert(
                ['codigo' => $sala['codigo']],
                [
                    'nombre' => $sala['nombre'],
                    'id_creador' => $sala['id_creador'],
                    'tiempo_respuesta' => $sala['tiempo_respuesta'],
                    'fecha_creacion' => $sala['fecha_creacion'],
                ]
            );
        }
    }
}
