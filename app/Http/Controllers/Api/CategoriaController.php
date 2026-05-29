<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = Categoria::with('salas');

        if ($request->filled('nombre')) {
            $query->where('nombre', 'like', '%' . $request->nombre . '%');
        }

        if ($request->filled('sala_id')) {
            $query->whereHas('salas', function ($q) use ($request) {
                $q->where('id', $request->sala_id);
            });
        }

        if ($request->filled('sort')) {
            $query->orderBy(
                $request->sort,
                $request->direction ?? 'asc'
            );
        }

        $perPage = $request->per_page ?? 10;

        return response()->json(
            $query->paginate($perPage),
            200
        );
    }


    public function store(StoreCategoriaRequest $request)
    {
        $this->authorize('categorias-crear');

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
        $this->authorize('categorias-editar');

        $categoria->update($request->validated());
        return response()->json($categoria);
    }

    public function destroy(Categoria $categoria)
    {
        $this->authorize('categorias-eliminar');

        $categoria->delete();
        return response()->json(null, 204);
    }

    public function getList()
    {
        return response()->json(Categoria::all());
    }
}
