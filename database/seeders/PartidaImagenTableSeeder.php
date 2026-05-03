<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartidaImagenTableSeeder extends Seeder
{
    public function run(): void
    {
        $partidas = DB::table('partidas')->pluck('id', 'fecha_inicio')->all();
        $imageIdsByUrl = DB::table('imagenes')->pluck('id', 'url')->all();
        $catalogRecords = GameImageCatalog::records();

        if (count($catalogRecords) < 5) {
            throw new RuntimeException('PartidaImagenTableSeeder: expected at least 5 images for demo linkage.');
        }

        $demoImageUrls = [
            $catalogRecords[3]['url'],
            $catalogRecords[4]['url'],
            $catalogRecords[2]['url'],
            $catalogRecords[0]['url'],
            $catalogRecords[1]['url'],
        ];

        $demoImageIds = [];
        foreach ($demoImageUrls as $url) {
            $imageId = $imageIdsByUrl[$url] ?? null;
            if (! is_int($imageId)) {
                throw new RuntimeException('PartidaImagenTableSeeder: missing image for url ' . $url . '.');
            }

            $demoImageIds[] = $imageId;
        }

        $partida1Id = $partidas['2026-04-15 10:00:00'] ?? null;
        $partida2Id = $partidas['2026-04-15 11:00:00'] ?? null;
        $partida3Id = $partidas['2026-04-15 12:00:00'] ?? null;

        if (! is_int($partida1Id) || ! is_int($partida2Id) || ! is_int($partida3Id)) {
            throw new RuntimeException('PartidaImagenTableSeeder: missing required partidas for demo linkage.');
        }

        $rows = [
            ['id_partida' => $partida1Id, 'id_imagen' => $demoImageIds[0], 'ronda' => 1],
            ['id_partida' => $partida1Id, 'id_imagen' => $demoImageIds[1], 'ronda' => 2],
            ['id_partida' => $partida2Id, 'id_imagen' => $demoImageIds[2], 'ronda' => 1],
            ['id_partida' => $partida3Id, 'id_imagen' => $demoImageIds[3], 'ronda' => 1],
            ['id_partida' => $partida3Id, 'id_imagen' => $demoImageIds[4], 'ronda' => 2],
        ];

        foreach ($rows as $row) {
            DB::table('partida_imagen')->updateOrInsert(
                [
                    'id_partida' => $row['id_partida'],
                    'id_imagen' => $row['id_imagen'],
                    'ronda' => $row['ronda'],
                ],
                []
            );
        }
    }
}
