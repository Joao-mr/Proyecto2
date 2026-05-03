<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('descripcion');
        });

        // Eliminar relaciones 
        DB::table('sala_categorias')->where('id_categoria', 1)->delete();
        DB::table('imagen_categoria')->where('id_categoria', 1)->delete();
        DB::table('categorias')->where('id', 1)->delete();

        // Asignar imágenes a las categorías restantes
        $imagenes = [
            'Deportes'    => '/images/deportes.webp',
            'Peliculas'   => '/images/pelicula.webp',
            'Videojuegos' => '/images/videojuegos.webp',
            'Geografia'   => '/images/geografia.webp',
        ];

        foreach ($imagenes as $nombre => $imagen) {
            DB::table('categorias')
                ->where('nombre', $nombre)
                ->update(['imagen' => $imagen]);
        }
    }

    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }
};
