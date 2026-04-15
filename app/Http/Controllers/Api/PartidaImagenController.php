<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartidaImagenRequest;
use Illuminate\Support\Facades\DB;

class PartidaImagenController extends Controller
{
    public function index()
    {
        $registros = DB::table('partida_imagen')
            ->join('partidas', 'partida_imagen.id_partida', '=', 'partidas.id')
            ->join('imagenes', 'partida_imagen.id_imagen', '=', 'imagenes.id')
            ->select('partida_imagen.*', 'imagenes.url', 'imagenes.respuesta_correcta')
            ->paginate(10);

        return response()->json($registros);
    }

    public function store(StorePartidaImagenRequest $request)
    {
        $data = $request->validated();

        DB::table('partida_imagen')->insert($data);

        return response()->json($data, 201);
    }

    public function show($idPartida)
    {
        $registros = DB::table('partida_imagen')
            ->join('imagenes', 'partida_imagen.id_imagen', '=', 'imagenes.id')
            ->where('partida_imagen.id_partida', $idPartida)
            ->select('partida_imagen.*', 'imagenes.url', 'imagenes.respuesta_correcta')
            ->orderBy('partida_imagen.ronda')
            ->get();

        return response()->json($registros);
    }

    public function destroy($idPartida, $idImagen)
    {
        DB::table('partida_imagen')
            ->where('id_partida', $idPartida)
            ->where('id_imagen', $idImagen)
            ->delete();

        return response()->json(null, 204);
    }
}
