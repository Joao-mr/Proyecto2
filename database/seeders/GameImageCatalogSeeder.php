<?php

namespace Database\Seeders;

use App\Support\GameImageCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameImageCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $records = GameImageCatalog::records();

        if ($records === []) {
            throw new \RuntimeException(
                'GameImageCatalogSeeder found zero catalog records. Verify image folders/files under public/images/Imagenes sala before seeding.'
            );
        }

        DB::transaction(function () use ($records): void {
            $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

            foreach ($records as $record) {
                $categoryId = $categoryIds[$record['category_name']] ?? null;

                if ($categoryId === null) {
                    throw new \RuntimeException(
                        sprintf(
                            'Missing category mapping for GameImageCatalogSeeder: category_name="%s", image_url="%s".',
                            $record['category_name'],
                            $record['url']
                        )
                    );
                }

                DB::table('imagenes')->updateOrInsert(
                    ['url' => $record['url']],
                    [
                        'respuesta_correcta' => $record['respuesta_correcta'],
                        'categoria_id' => $categoryId,
                    ]
                );
            }
        });
    }
}
