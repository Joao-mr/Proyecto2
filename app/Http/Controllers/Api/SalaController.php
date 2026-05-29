<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaRequest;
use App\Http\Requests\UpdateSalaRequest;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SalaController extends Controller
{
    public function index(Request $request)
    {
        $requestedSort = (string) $request->get('sort', 'created_at');
        $direction = strtolower((string) $request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sort = Schema::hasColumn('salas', $requestedSort) ? $requestedSort : 'id';

        $salas = Sala::with('categorias')
            ->when($request->filled('search'), fn($q) =>
                $q->where('nombre', 'like', "%{$request->search}%")
                  ->orWhere('codigo', 'like', "%{$request->search}%"))
            ->when($request->filled('categoria_id'), fn($q) =>
                $q->whereHas('categorias', fn($qq) =>
                    $qq->where('id', $request->categoria_id)))
            ->orderBy($sort, $direction)
            ->paginate($request->get('per_page', 10));

        return response()->json($salas);
    }

    public function store(StoreSalaRequest $request)
    {
        $data = $request->validated();
        $categorias = $data['categorias'] ?? [];
        unset($data['categorias']);

        $data['id_creador'] = $request->user()->id;

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
        $this->authorizeSalaChange($request, $sala, 'salas-editar');

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

    public function destroy(Request $request, Sala $sala)
    {
        $this->authorizeSalaChange($request, $sala, 'salas-eliminar');

        $sala->delete();
        return response()->json(null, 204);
    }

    private function authorizeSalaChange(Request $request, Sala $sala, string $permission): void
    {
        if ($request->user()->id === $sala->id_creador) {
            return;
        }

        $this->authorize($permission);
    }
}
