<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagenesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('partida_imagen')->delete();
        DB::table('imagen_categoria')->delete();
        DB::table('imagenes')->delete();

        $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

        $rows = collect(GameImageCatalog::records())
            ->filter(static fn (array $record): bool => isset($categoryIds[$record['category_name']]))
            ->values()
            ->map(static function (array $record, int $index) use ($categoryIds): array {
                return [
                    'id' => $index + 1,
                    'url' => $record['url'],
                    'respuesta_correcta' => $record['respuesta_correcta'],
                    'categoria_id' => $categoryIds[$record['category_name']],
                ];
            })
            ->all();

        if ($rows !== []) {
            DB::table('imagenes')->insert($rows);
        }
    }
}
