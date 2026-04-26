<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRespuestaRequest;
use App\Http\Requests\UpdateRespuestaRequest;
use App\Models\Respuesta;
use Illuminate\Support\Facades\Auth;

class RespuestaController extends Controller
{
    public function index()
    {
        $respuestas = Respuesta::with(['usuario:id,name', 'imagen:id,url,respuesta_correcta'])->paginate(10);
        return response()->json($respuestas);
    }

    public function store(StoreRespuestaRequest $request)
    {
        $data = $request->validated();
        $data['id_usuario'] = Auth::id();

        $respuesta = Respuesta::create($data);
        $respuesta->load(['usuario:id,name', 'imagen:id,url,respuesta_correcta']);

        return response()->json($respuesta, 201);
    }

    public function show(Respuesta $respuesta)
    {
        $respuesta->load(['usuario:id,name', 'imagen:id,url,respuesta_correcta']);
        return response()->json($respuesta);
    }

    public function update(UpdateRespuestaRequest $request, Respuesta $respuesta)
    {
        $respuesta->update($request->validated());
        $respuesta->load(['usuario:id,name', 'imagen:id,url,respuesta_correcta']);

        return response()->json($respuesta);
    }

    public function destroy(Respuesta $respuesta)
    {
        $respuesta->delete();
        return response()->json(null, 204);
    }
}
