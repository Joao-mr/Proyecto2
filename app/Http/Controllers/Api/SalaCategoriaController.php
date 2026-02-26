<?php
namespace App\Http\Controllers\Api;

use App\Http\Resources\SalaCategoriaResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaCategoriaRequest;
use App\Http\Requests\UpdateSalaCategoriaRequest;
use App\Models\SalaCategoria;

class SalaCategoriaController extends Controller
{
    /**
     * Devuelve los datos del usuario autenticado.
     * GET /api/sala-categorias/user
     */
    public function user()
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('No autenticado', 401);
        }
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                // Puedes agregar más campos si lo necesitas
            ]
        ], 200);
    }
    // Helper para errores JSON
    protected function errorResponse($message, $code = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        if ($errors) {
            $response['errors'] = $errors;
        }
        return response()->json($response, $code);
    }
    public function index()
    {
        $this->authorize('viewAny', SalaCategoria::class);
        $query = SalaCategoria::with(['sala', 'categoria']);

        // Ejemplo de join para obtener nombre de sala y categoria directamente
        // $query = $query->join('salas', 'salas.id', '=', 'sala_categorias.id_sala')
        //     ->join('categorias', 'categorias.id', '=', 'sala_categorias.id_categoria')
        //     ->select('sala_categorias.*', 'salas.nombre as sala_nombre', 'categorias.nombre as categoria_nombre');

        // Filtrado directo por id_sala y id_categoria
        if (request('id_sala')) {
            $query->where('id_sala', request('id_sala'));
        }
        if (request('id_categoria')) {
            $query->where('id_categoria', request('id_categoria'));
        }

        // Filtrado avanzado: buscar sala por nombre y categoría por nombre (whereHas)
        if ($searchSala = request('sala')) {
            $query->whereHas('sala', function ($q) use ($searchSala) {
                $q->where('nombre', 'like', "%$searchSala%");
            });
        }
        if ($searchCategoria = request('categoria')) {
            $query->whereHas('categoria', function ($q) use ($searchCategoria) {
                $q->where('nombre', 'like', "%$searchCategoria%");
            });
        }

        // Ejemplo de combinación de filtros: sala por nombre y categoría por nombre
        // $query->whereHas('sala', fn($q) => $q->where('nombre', 'like', '%Aula%'))
        //       ->whereHas('categoria', fn($q) => $q->where('nombre', 'like', '%Matemáticas%'));

        // Ordenación
        $sort = request('sort', 'id_sala');
        $direction = request('direction', 'asc');
        $allowedSorts = ['id_sala', 'id_categoria', 'created_at', 'updated_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id_sala';
        }
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }

        // Paginación
        $perPage = (int) request('per_page', 20);
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 20;
        }

        $registros = $query->orderBy($sort, $direction)->paginate($perPage);

        // Ejemplo attach/sync para relaciones N:M (en modelos Sala y Categoria):
        // $sala->categorias()->attach($categoriaId);
        // $sala->categorias()->sync([$categoriaId1, $categoriaId2]);

        return response()->json([
            'success' => true,
            'data' => SalaCategoriaResource::collection($registros),
            'links' => [
                'first' => $registros->url(1),
                'last' => $registros->url($registros->lastPage()),
                'prev' => $registros->previousPageUrl(),
                'next' => $registros->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $registros->currentPage(),
                'from' => $registros->firstItem(),
                'last_page' => $registros->lastPage(),
                'per_page' => $registros->perPage(),
                'to' => $registros->lastItem(),
                'total' => $registros->total(),
            ]
        ], 200);
    }

    public function store(StoreSalaCategoriaRequest $request)
    {
        $this->authorize('create', SalaCategoria::class);
        $registro = SalaCategoria::create($request->validated());

        // Gestión de imagen con Spatie Media Library
        if ($request->hasFile('image')) {
            $registro->addMediaFromRequest('image')->toMediaCollection('images');
        }

        $registro->load(['sala', 'categoria']);
        return response()->json([
            'success' => true,
            'data' => new SalaCategoriaResource($registro)
        ], 201);
    }

    public function show($id_sala, $id_categoria)
    {
        try {
            $registro = $this->findRegistro($id_sala, $id_categoria);
            $this->authorize('view', $registro);
            $registro->load(['sala', 'categoria']);
            return response()->json([
                'success' => true,
                'data' => new SalaCategoriaResource($registro)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Relación sala-categoria no encontrada', 404);
        }
    }

    public function update(UpdateSalaCategoriaRequest $request, $id_sala, $id_categoria)
    {
        try {
            $registroActual = $this->findRegistro($id_sala, $id_categoria);
            $this->authorize('update', $registroActual);
            $data = $request->validated();
            $nuevoRegistroExiste = SalaCategoria::where('id_sala', $data['id_sala'])
                ->where('id_categoria', $data['id_categoria'])
                ->exists();
            $esMismoRegistro = ((int) $id_sala === (int) $data['id_sala'])
                && ((int) $id_categoria === (int) $data['id_categoria']);
            if ($nuevoRegistroExiste && !$esMismoRegistro) {
                return $this->errorResponse('La relación sala-categoria ya existe.', 422);
            }
            if (!$esMismoRegistro) {
                SalaCategoria::where('id_sala', $id_sala)
                    ->where('id_categoria', $id_categoria)
                    ->delete();
                $registroActual = SalaCategoria::create($data);
            }
            $registroActual->load(['sala', 'categoria']);
            return response()->json([
                'success' => true,
                'data' => new SalaCategoriaResource($registroActual)
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Relación sala-categoria no encontrada', 404);
        }
    }

    public function destroy($id_sala, $id_categoria)
    {
        try {
            $registro = $this->findRegistro($id_sala, $id_categoria);
            $this->authorize('delete', $registro);
            SalaCategoria::where('id_sala', $id_sala)
                ->where('id_categoria', $id_categoria)
                ->delete();
            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Relación sala-categoria no encontrada', 404);
        }
    }

    private function findRegistro($id_sala, $id_categoria): SalaCategoria
    {
        return SalaCategoria::where('id_sala', $id_sala)
            ->where('id_categoria', $id_categoria)
            ->firstOrFail();
    }
}