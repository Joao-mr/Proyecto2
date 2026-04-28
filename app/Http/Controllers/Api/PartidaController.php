<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartidaResultadoRequest;
use App\Http\Requests\StorePartidaRequest;
use App\Http\Requests\UpdatePartidaRequest;
use App\Models\Sala;
use App\Models\Partida;
use App\Services\UserStatsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartidaController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $partidas = Partida::with('sala:id,nombre,codigo')->paginate($perPage);
        return response()->json($partidas);
    }

    public function store(StorePartidaRequest $request)
    {
        $partida = Partida::create($request->validated());
        $partida->load('sala:id,nombre,codigo');

        return response()->json($partida, 201);
    }

    public function storeResult(StorePartidaResultadoRequest $request, UserStatsService $userStatsService)
    {
        $data = $request->validated();
        $userId = (int) $request->user()->id;
        $idSala = $this->resolveSalaIdForResult($data, $userId);

        $partida = DB::transaction(function () use ($data, $idSala, $userId): Partida {
            $partida = Partida::create([
                'id_sala' => $idSala,
                'fecha_inicio' => $data['fecha_inicio'] ?? now(),
                'fecha_fin' => $data['fecha_fin'] ?? now(),
            ]);

            DB::table('usuario_partida')->updateOrInsert(
                [
                    'id_usuario' => $userId,
                    'id_partida' => $partida->id,
                ],
                [
                    'puntuacion' => (int) $data['puntuacion'],
                ]
            );

            return $partida;
        });

        $userStatsService->syncForUser($userId);

        return response()->json([
            'id_partida' => $partida->id,
            'id_sala' => $idSala,
            'puntuacion' => (int) $data['puntuacion'],
        ], 201);
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

    private function resolveSalaIdForResult(array $data, int $userId): int
    {
        if (!empty($data['id_sala'])) {
            return (int) $data['id_sala'];
        }

        $idCategoria = (int) ($data['id_categoria'] ?? 0);
        if ($idCategoria <= 0) {
            abort(422, 'id_categoria no es valido.');
        }

        $existingSalaId = DB::table('sala_categorias')
            ->where('id_categoria', $idCategoria)
            ->orderBy('id_sala')
            ->value('id_sala');

        if ($existingSalaId) {
            return (int) $existingSalaId;
        }

        $sala = Sala::create([
            'nombre' => 'Sala técnica categoría ' . $idCategoria,
            'codigo' => $this->generateUniqueSalaCode(),
            'id_creador' => $userId,
            'tiempo_respuesta' => 30,
        ]);

        DB::table('sala_categorias')->insert([
            'id_sala' => $sala->id,
            'id_categoria' => $idCategoria,
        ]);

        return (int) $sala->id;
    }

    private function generateUniqueSalaCode(): string
    {
        do {
            $code = 'AUTO-' . Str::upper(Str::random(8));
        } while (Sala::query()->where('codigo', $code)->exists());

        return $code;
    }
}
