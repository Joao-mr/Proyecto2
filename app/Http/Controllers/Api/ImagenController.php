<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImagenRequest;
use App\Http\Requests\UpdateImagenRequest;
use App\Models\Imagen;

class ImagenController extends Controller
{
    public function index()
    {
        $imagenes = Imagen::paginate(10);
        return response()->json($imagenes);
    }

    public function store(StoreImagenRequest $request)
    {
        $imagen = Imagen::create($request->validated());
        return response()->json($imagen, 201);
    }

    public function show(Imagen $imagen)
    {
        return response()->json($imagen);
    }

    public function update(UpdateImagenRequest $request, Imagen $imagen)
    {
        $imagen->update($request->validated());
        return response()->json($imagen);
    }

    public function destroy(Imagen $imagen)
    {
        $imagen->delete();
        return response()->json(null, 204);
    }

    public function getList()
    {
        return response()->json(Imagen::all());
    }
}
