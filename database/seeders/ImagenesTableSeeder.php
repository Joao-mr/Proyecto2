<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagenesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('imagenes')->delete();

        DB::table('imagenes')->insert([
            [
                'id' => 1,
                'url' => 'https://placehold.co/600x400?text=Gato',
                'respuesta_correcta' => 'gato',
            ],
            [
                'id' => 2,
                'url' => 'https://placehold.co/600x400?text=Perro',
                'respuesta_correcta' => 'perro',
            ],
            [
                'id' => 3,
                'url' => 'https://placehold.co/600x400?text=Futbol',
                'respuesta_correcta' => 'futbol',
            ],
            [
                'id' => 4,
                'url' => 'https://placehold.co/600x400?text=SpiderMan',
                'respuesta_correcta' => 'spiderman',
            ],
            [
                'id' => 5,
                'url' => 'https://placehold.co/600x400?text=Minecraft',
                'respuesta_correcta' => 'minecraft',
            ],
        ]);
    }
}
