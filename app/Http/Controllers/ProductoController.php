<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $productos = Producto::with('categoria')
            ->when($request->buscar, fn($q) => $q->where('nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->categoria, fn($q) => $q->where('categoria_id', $request->categoria))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'categorias' => Categoria::where('activo', true)->get(),
            'filtros' => $request->only(['buscar', 'categoria']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Productos/Form', [
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:productos',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string',
        ]);

        Producto::create($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto creado correctamente');
    }

    public function edit(Producto $producto)
    {
        return Inertia::render('Productos/Form', [
            'producto' => $producto,
            'categorias' => Categoria::where('activo', true)->get(),
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:productos,codigo,' . $producto->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'unidad_medida' => 'required|string',
            'activo' => 'boolean',
        ]);

        $producto->update($validated);

        return redirect()->route('productos.index')
            ->with('success', 'Producto actualizado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);

        return redirect()->route('productos.index')
            ->with('success', 'Producto desactivado correctamente');
    }
}
