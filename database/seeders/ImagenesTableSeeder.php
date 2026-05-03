<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImagenesTableSeeder extends Seeder
{
    public function run(): void
    {
        $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

        foreach (GameImageCatalog::records() as $record) {
            $categoryId = $categoryIds[$record['category_name']] ?? null;

            if (! is_int($categoryId)) {
                throw new RuntimeException('ImagenesTableSeeder: missing category for image record.');
            }

            DB::table('imagenes')->updateOrInsert(
                ['url' => $record['url']],
                [
                    'respuesta_correcta' => $record['respuesta_correcta'],
                    'categoria_id' => $categoryId,
                ]
            );
        }
    }
}
