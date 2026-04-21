<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUsuarioSalaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioSalaController extends Controller
{
    public function index()
    {
        $registros = DB::table('usuario_sala')
            ->join('users', 'usuario_sala.id_usuario', '=', 'users.id')
            ->join('salas', 'usuario_sala.id_sala', '=', 'salas.id')
            ->select('usuario_sala.*', 'users.name as usuario_name', 'salas.nombre as sala_nombre')
            ->paginate(10);

        return response()->json($registros);
    }

    public function store(StoreUsuarioSalaRequest $request)
    {
        $data = $request->validated();
        $data['id_usuario'] = Auth::id();
        $data['fecha_entrada'] = now();

        DB::table('usuario_sala')->insert($data);

        return response()->json($data, 201);
    }

    public function show($idSala)
    {
        $registros = DB::table('usuario_sala')
            ->join('users', 'usuario_sala.id_usuario', '=', 'users.id')
            ->where('usuario_sala.id_sala', $idSala)
            ->select('usuario_sala.*', 'users.name as usuario_name')
            ->get();

        return response()->json($registros);
    }

    public function destroy($idSala)
    {
        $idUsuario = Auth::id();

        DB::table('usuario_sala')
            ->where('id_usuario', $idUsuario)
            ->where('id_sala', $idSala)
            ->delete();

        return response()->json(null, 204);
    }
}
