<?php

use App\Support\GameImageCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('categorias') || ! Schema::hasTable('imagenes')) {
            return;
        }

        DB::transaction(function (): void {
            $categoryIds = DB::table('categorias')->pluck('id', 'nombre')->all();

            foreach (GameImageCatalog::records() as $record) {
                $categoryId = $categoryIds[$record['category_name']] ?? null;

                if (! $categoryId) {
                    continue;
                }

                DB::table('imagenes')->updateOrInsert(
                    ['url' => $record['url']],
                    [
                        'respuesta_correcta' => $record['respuesta_correcta'],
                        'categoria_id' => $categoryId,
                    ]
                );

                if (! Schema::hasTable('imagen_categoria')) {
                    continue;
                }

                $imageId = DB::table('imagenes')
                    ->where('url', $record['url'])
                    ->value('id');

                if (! $imageId) {
                    continue;
                }

                DB::table('imagen_categoria')->updateOrInsert(
                    [
                        'id_imagen' => $imageId,
                        'id_categoria' => $categoryId,
                    ],
                    []
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('imagenes')) {
            return;
        }

        DB::transaction(function (): void {
            foreach (GameImageCatalog::records() as $record) {
                $imageId = DB::table('imagenes')
                    ->where('url', $record['url'])
                    ->value('id');

                if ($imageId && Schema::hasTable('imagen_categoria')) {
                    DB::table('imagen_categoria')->where('id_imagen', $imageId)->delete();
                }

                DB::table('imagenes')->where('url', $record['url'])->delete();
            }
        });
    }
};