<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaRequest;
use App\Http\Requests\UpdateSalaRequest;
use App\Models\Sala;

class SalaController extends Controller
{
    public function index()
    {
        $salas = Sala::with('categorias')->paginate(10);
        return response()->json($salas);
    }

    public function store(StoreSalaRequest $request)
    {
        $data = $request->validated();
        $categorias = $data['categorias'] ?? [];
        unset($data['categorias']);

        $sala = Sala::create($data);
        $sala->categorias()->sync($categorias);
        $sala->load('categorias');

        return response()->json($sala, 201);
    }

    public function show(Sala $sala)
    {
        $sala->load('categorias');
        return response()->json($sala);
    }

    public function update(UpdateSalaRequest $request, Sala $sala)
    {
        $data = $request->validated();
        $categorias = $data['categorias'] ?? null;
        unset($data['categorias']);

        if (!empty($data)) {
            $sala->update($data);
        }

        if (!is_null($categorias)) {
            $sala->categorias()->sync($categorias);
        }

        $sala->load('categorias');

        return response()->json($sala);
    }

    public function destroy(Sala $sala)
    {
        $sala->delete();
        return response()->json(null, 204);
    }
}


///aaaaaaaaaaaaaaaa