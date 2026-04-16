<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioPartidaRequest;
use App\Http\Requests\UpdateUsuarioPartidaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioPartidaController extends Controller
{
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
        $data['id_usuario'] = Auth::id();

        DB::table('usuario_partida')->insert($data);

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

        DB::table('usuario_partida')
            ->where('id_usuario', $idUsuario)
            ->where('id_partida', $idPartida)
            ->update($data);

        return response()->json(['message' => 'Actualizado correctamente']);
    }

    public function destroy($idPartida)
    {
        $idUsuario = Auth::id();

        DB::table('usuario_partida')
            ->where('id_usuario', $idUsuario)
            ->where('id_partida', $idPartida)
            ->delete();

        return response()->json(null, 204);
    }
}
