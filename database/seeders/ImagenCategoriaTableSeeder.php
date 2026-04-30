<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImagenCategoriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('imagen_categoria')->delete();

        $imageIds = DB::table('imagenes')->pluck('id', 'url')->all();
        $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

        $rows = collect(GameImageCatalog::records())
            ->map(static function (array $record) use ($imageIds, $categoryIds): ?array {
                $imageId = $imageIds[$record['url']] ?? null;
                $categoryId = $categoryIds[$record['category_name']] ?? null;

                if (! $imageId || ! $categoryId) {
                    return null;
                }

                return [
                    'id_imagen' => $imageId,
                    'id_categoria' => $categoryId,
                ];
            })
            ->filter()
            ->unique(static fn (array $row): string => $row['id_imagen'] . ':' . $row['id_categoria'])
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('imagen_categoria')->insert($rows);
        }
    }
}
