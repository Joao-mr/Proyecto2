<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;

class CategoriaController extends Controller
{//holaa
    public function index()
    {
        $categorias = Categoria::with('salas')->paginate(10);
        return response()->json($categorias);
    }

    public function store(StoreCategoriaRequest $request)
    {
        $categoria = Categoria::create($request->validated());
        return response()->json($categoria, 201);
    }

    public function show(Categoria $categoria)
    {
        $categoria->load('salas');
        return response()->json($categoria);
    }

    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());
        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $categoria->delete();
        return response()->json(null, 204);
    }
}
