<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSalaCategoriaRequest;
use App\Http\Requests\UpdateSalaCategoriaRequest;
use App\Models\SalaCategoria;

class SalaCategoriaController extends Controller
{
    public function index()
    {
        $registros = SalaCategoria::with(['sala', 'categoria'])->paginate(10);
        return response()->json($registros);
    }

    public function store(StoreSalaCategoriaRequest $request)
    {
        $registro = SalaCategoria::create($request->validated());
        $registro->load(['sala', 'categoria']);

        return response()->json($registro, 201);
    }

    public function show($id_sala, $id_categoria)
    {
        $registro = $this->findRegistro($id_sala, $id_categoria);
        $registro->load(['sala', 'categoria']);

        return response()->json($registro);
    }

    public function update(UpdateSalaCategoriaRequest $request, $id_sala, $id_categoria)
    {
        $registroActual = $this->findRegistro($id_sala, $id_categoria);
        $data = $request->validated();

        $nuevoRegistroExiste = SalaCategoria::where('id_sala', $data['id_sala'])
            ->where('id_categoria', $data['id_categoria'])
            ->exists();

        $esMismoRegistro = ((int) $id_sala === (int) $data['id_sala'])
            && ((int) $id_categoria === (int) $data['id_categoria']);

        if ($nuevoRegistroExiste && !$esMismoRegistro) {
            return response()->json([
                'message' => 'La relación sala-categoria ya existe.',
            ], 422);
        }

        if (!$esMismoRegistro) {
            SalaCategoria::where('id_sala', $id_sala)
                ->where('id_categoria', $id_categoria)
                ->delete();
            $registroActual = SalaCategoria::create($data);
        }

        $registroActual->load(['sala', 'categoria']);

        return response()->json($registroActual);
    }

    public function destroy($id_sala, $id_categoria)
    {
        $this->findRegistro($id_sala, $id_categoria);

        SalaCategoria::where('id_sala', $id_sala)
            ->where('id_categoria', $id_categoria)
            ->delete();

        return response()->json(null, 204);
    }

    private function findRegistro($id_sala, $id_categoria): SalaCategoria
    {
        return SalaCategoria::where('id_sala', $id_sala)
            ->where('id_categoria', $id_categoria)
            ->firstOrFail();
    }
}