<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImagenCategoriaRequest;
use Illuminate\Support\Facades\DB;

class ImagenCategoriaController extends Controller
{
    public function index()
    {
        $registros = DB::table('imagen_categoria')
            ->join('imagenes', 'imagen_categoria.id_imagen', '=', 'imagenes.id')
            ->join('categorias', 'imagen_categoria.id_categoria', '=', 'categorias.id')
            ->select('imagen_categoria.*', 'imagenes.url', 'categorias.nombre as categoria_nombre')
            ->paginate(10);

        return response()->json($registros);
    }

    public function store(StoreImagenCategoriaRequest $request)
    {
        $data = $request->validated();

        DB::table('imagen_categoria')->insert($data);

        return response()->json($data, 201);
    }

    public function show($idImagen)
    {
        $registros = DB::table('imagen_categoria')
            ->join('categorias', 'imagen_categoria.id_categoria', '=', 'categorias.id')
            ->where('imagen_categoria.id_imagen', $idImagen)
            ->select('imagen_categoria.*', 'categorias.nombre as categoria_nombre')
            ->get();

        return response()->json($registros);
    }

    public function destroy($idImagen, $idCategoria)
    {
        DB::table('imagen_categoria')
            ->where('id_imagen', $idImagen)
            ->where('id_categoria', $idCategoria)
            ->delete();

        return response()->json(null, 204);
    }
}
