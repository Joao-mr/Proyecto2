<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ImagenesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        \DB::table('imagenes')->delete();

        \DB::table('imagenes')->insert(array (
            0 =>
            array (
                'id' => 1,
                'url' => 'https://placehold.co/600x400?text=Gato',
                'respuesta_correcta' => 'gato',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            1 =>
            array (
                'id' => 2,
                'url' => 'https://placehold.co/600x400?text=Perro',
                'respuesta_correcta' => 'perro',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            2 =>
            array (
                'id' => 3,
                'url' => 'https://placehold.co/600x400?text=Futbol',
                'respuesta_correcta' => 'futbol',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            3 =>
            array (
                'id' => 4,
                'url' => 'https://placehold.co/600x400?text=SpiderMan',
                'respuesta_correcta' => 'spiderman',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
            4 =>
            array (
                'id' => 5,
                'url' => 'https://placehold.co/600x400?text=Minecraft',
                'respuesta_correcta' => 'minecraft',
                'created_at' => '2026-04-15 10:00:00',
                'updated_at' => '2026-04-15 10:00:00',
            ),
        ));
    }
}
