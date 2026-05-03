<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoGameSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SalasTableSeeder::class,
            PartidasTableSeeder::class,
            UsuarioPartidaTableSeeder::class,
            UsuarioSalaTableSeeder::class,
            PartidaImagenTableSeeder::class,
        ]);
    }
}
