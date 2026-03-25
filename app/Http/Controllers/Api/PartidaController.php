<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartidaRequest;
use App\Http\Requests\UpdatePartidaRequest;
use App\Models\Partida;

class PartidaController extends Controller
{
    public function index()
    {
        $partidas = Partida::with('sala:id,nombre,codigo')->paginate(10);
        return response()->json($partidas);
    }

    public function store(StorePartidaRequest $request)
    {
        $partida = Partida::create($request->validated());
        $partida->load('sala:id,nombre,codigo');

        return response()->json($partida, 201);
    }

    public function show(Partida $partida)
    {
        $partida->load('sala:id,nombre,codigo');
        return response()->json($partida);
    }

    public function update(UpdatePartidaRequest $request, Partida $partida)
    {
        $partida->update($request->validated());
        $partida->load('sala:id,nombre,codigo');

        return response()->json($partida);
    }

    public function destroy(Partida $partida)
    {
        $partida->delete();
        return response()->json(null, 204);
    }
}
