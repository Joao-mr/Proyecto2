<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'surname1' => 'Demo',
                'surname2' => null,
                'alias' => 'admin',
                'email' => 'admin@demo.com',
                'rol' => 'admin',
            ],
            [
                'name' => 'User',
                'surname1' => 'Demo',
                'surname2' => null,
                'alias' => 'user',
                'email' => 'user@demo.com',
                'rol' => 'player',
            ],
        ];

        foreach ($users as $user) {
            $existingUser = DB::table('users')
                ->where('email', $user['email'])
                ->first(['id', 'password']);

            $payload = [
                'name' => $user['name'],
                'surname1' => $user['surname1'],
                'surname2' => $user['surname2'],
                'alias' => $user['alias'],
                'rol' => $user['rol'],
                'email_verified_at' => null,
                'remember_token' => null,
                'elo' => 0,
                'partidas_jugadas' => 0,
                'titulo' => 'Novato',
                'elo_total' => 0,
                'imagenes_acertadas' => 0,
                'promedio_puntos' => 0,
                'mejor_puntuacion' => 0,
                'ultima_puntuacion' => 0,
                'consistencia_pct' => 0,
            ];

            if ($existingUser === null) {
                DB::table('users')->insert([
                    ...$payload,
                    'email' => $user['email'],
                    'password' => Hash::make('12345678'),
                ]);
                continue;
            }

            if (! is_string($existingUser->password) || trim($existingUser->password) === '') {
                $payload['password'] = Hash::make('12345678');
            }

            DB::table('users')
                ->where('id', $existingUser->id)
                ->update($payload);
        }
    }
}
