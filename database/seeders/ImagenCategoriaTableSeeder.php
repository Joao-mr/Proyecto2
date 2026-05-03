<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImagenCategoriaTableSeeder extends Seeder
{
    public function run(): void
    {
        $imageIds = DB::table('imagenes')->pluck('id', 'url')->all();
        $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

        foreach (GameImageCatalog::records() as $record) {
            $imageId = $imageIds[$record['url']] ?? null;
            $categoryId = $categoryIds[$record['category_name']] ?? null;

            if (! is_int($imageId) || ! is_int($categoryId)) {
                throw new RuntimeException(
                    'ImagenCategoriaTableSeeder: missing id mapping for url ' . $record['url'] . ' and category ' . $record['category_name'] . '.'
                );
            }

            DB::table('imagen_categoria')->updateOrInsert(
                ['id_imagen' => $imageId, 'id_categoria' => $categoryId],
                []
            );
        }
    }
}
