<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    public function index()
    {
        return Inertia::render('Categorias/Index', [
            'categorias' => Categoria::withCount('productos')->orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string',
        ]);

        Categoria::create($validated);

        return back()->with('success', 'Categoría creada correctamente');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $categoria->update($validated);

        return back()->with('success', 'Categoría actualizada correctamente');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error', 'No se puede eliminar una categoría con productos');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada correctamente');
    }
}
