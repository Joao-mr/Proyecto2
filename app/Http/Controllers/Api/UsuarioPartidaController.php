<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioPartidaRequest;
use App\Http\Requests\UpdateUsuarioPartidaRequest;
use App\Models\Partida;
use App\Models\User;
use App\Services\UserStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UsuarioPartidaController extends Controller
{
    public function __construct(private readonly UserStatsService $userStatsService)
    {
    }

    public function index()
    {
        $registros = DB::table('usuario_partida')
            ->join('users', 'usuario_partida.id_usuario', '=', 'users.id')
            ->join('partidas', 'usuario_partida.id_partida', '=', 'partidas.id')
            ->select('usuario_partida.*', 'users.name as usuario_name', 'partidas.id_sala')
            ->paginate(10);

        return response()->json($registros);
    }

    public function store(StoreUsuarioPartidaRequest $request)
    {
        $data = $request->validated();
        $idUsuario = Auth::id();
        $data['id_usuario'] = $idUsuario;

        DB::transaction(function () use ($data, $idUsuario) {
            DB::table('usuario_partida')->updateOrInsert(
                [
                    'id_usuario' => $data['id_usuario'],
                    'id_partida' => $data['id_partida'],
                ],
                [
                    'puntuacion' => $data['puntuacion'],
                ]
            );
        });

        $this->userStatsService->syncForUser((int) $data['id_usuario']);

        return response()->json($data, 201);
    }

    public function show($idPartida)
    {
        $registros = DB::table('usuario_partida')
            ->join('users', 'usuario_partida.id_usuario', '=', 'users.id')
            ->where('usuario_partida.id_partida', $idPartida)
            ->select('usuario_partida.*', 'users.name as usuario_name')
            ->get();

        return response()->json($registros);
    }

    public function update(UpdateUsuarioPartidaRequest $request, $idPartida)
    {
        $data = $request->validated();
        $idUsuario = Auth::id();

        DB::transaction(function () use ($data, $idUsuario, $idPartida) {
            DB::table('usuario_partida')
                ->where('id_usuario', $idUsuario)
                ->where('id_partida', $idPartida)
                ->update($data);
        });

        $this->userStatsService->syncForUser((int) $idUsuario);

        return response()->json(['message' => 'Actualizado correctamente']);
    }

    public function destroy($idPartida)
    {
        $idUsuario = Auth::id();

        DB::transaction(function () use ($idUsuario, $idPartida) {
            DB::table('usuario_partida')
                ->where('id_usuario', $idUsuario)
                ->where('id_partida', $idPartida)
                ->delete();
        });

        $this->userStatsService->syncForUser((int) $idUsuario);

        return response()->json(null, 204);
    }

    public function finish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'puntuacion' => ['required', 'integer', 'min:0'],
            'id_sala' => ['nullable', 'integer', 'exists:salas,id'],
            'id_categoria' => ['nullable', 'integer', 'exists:categorias,id'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('id_sala') && !$request->filled('id_categoria')) {
                $validator->errors()->add('id_sala', 'Debes enviar id_sala o id_categoria.');
            }
        });

        $validated = $validator->validate();

        $idUsuario = Auth::id();
        $result = DB::transaction(function () use ($validated, $idUsuario) {
            $idSala = isset($validated['id_sala']) ? (int) $validated['id_sala'] : null;

            if (!$idSala && !empty($validated['id_categoria'])) {
                $idSala = DB::table('sala_categorias')
                    ->where('id_categoria', (int) $validated['id_categoria'])
                    ->orderBy('id_sala')
                    ->value('id_sala');
            }

            if (!$idSala) {
                abort(422, 'No se pudo resolver una sala para guardar la partida.');
            }

            $partida = Partida::create([
                'id_sala' => $idSala,
                'fecha_inicio' => $validated['fecha_inicio'] ?? now(),
                'fecha_fin' => $validated['fecha_fin'] ?? now(),
            ]);

            DB::table('usuario_partida')->updateOrInsert(
                [
                    'id_usuario' => $idUsuario,
                    'id_partida' => $partida->id,
                ],
                [
                    'puntuacion' => (int) $validated['puntuacion'],
                ]
            );

            return [
                'partida_id' => $partida->id,
                'id_sala' => $idSala,
            ];
        });

        $this->userStatsService->syncForUser((int) $idUsuario);
        $user = User::query()->findOrFail($idUsuario);

        $result['elo'] = (int) ($user->elo ?? $user->elo_total ?? 0);
        $result['partidas_jugadas'] = (int) ($user->partidas_jugadas ?? 0);
        $result['titulo'] = $user->titulo;

        return response()->json($result, 201);
    }
}
