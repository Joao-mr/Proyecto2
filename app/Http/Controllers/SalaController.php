<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalaController extends Controller
{
    // ...existing code...
    public function index()
    {
        $salas = \App\Models\Sala::all();
        return view('admin.salas.index', compact('salas'));
    }

    public function create()
    {
        return view('admin.salas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);
        \App\Models\Sala::create($validated);
        return redirect()->route('admin.salas.index')->with('success', 'Sala creada correctamente.');
    }

    public function edit(\App\Models\Sala $sala)
        {
            $categorias = \App\Models\Categoria::all();
            return view('admin.salas.edit', compact('sala', 'categorias'));
        }

    public function update(Request $request, \App\Models\Sala $sala)
        {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'codigo' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'categorias' => 'array',
                'categorias.*' => 'exists:categorias,id',
            ]);
            $sala->update($validated);
            $sala->categorias()->sync($request->input('categorias', []));
            return redirect()->route('admin.salas.index')->with('success', 'Sala actualizada con éxito');
        }

    public function destroy(\App\Models\Sala $sala)
    {
        $sala->delete();
        return redirect()->route('admin.salas.index')->with('success', 'Sala eliminada correctamente.');
    }
}
