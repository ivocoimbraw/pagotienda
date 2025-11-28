<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@tiendadame.com',
            'password' => Hash::make('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        // Crear vendedor
        User::create([
            'name' => 'Vendedor',
            'email' => 'vendedor@tiendadame.com',
            'password' => Hash::make('password'),
            'rol' => 'vendedor',
            'activo' => true,
        ]);

        // Crear categorías
        $categorias = [
            ['nombre' => 'Electrónicos', 'descripcion' => 'Dispositivos electrónicos'],
            ['nombre' => 'Ropa', 'descripcion' => 'Prendas de vestir'],
            ['nombre' => 'Alimentos', 'descripcion' => 'Productos alimenticios'],
            ['nombre' => 'Hogar', 'descripcion' => 'Artículos para el hogar'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }

        // Crear productos de ejemplo
        $productos = [
            ['codigo' => 'ELEC001', 'nombre' => 'Audífonos Bluetooth', 'categoria_id' => 1, 'precio_compra' => 50, 'precio_venta' => 80, 'stock' => 25],
            ['codigo' => 'ELEC002', 'nombre' => 'Cargador USB-C', 'categoria_id' => 1, 'precio_compra' => 15, 'precio_venta' => 30, 'stock' => 50],
            ['codigo' => 'ROPA001', 'nombre' => 'Camiseta Algodón', 'categoria_id' => 2, 'precio_compra' => 20, 'precio_venta' => 45, 'stock' => 100],
            ['codigo' => 'ROPA002', 'nombre' => 'Pantalón Jean', 'categoria_id' => 2, 'precio_compra' => 40, 'precio_venta' => 85, 'stock' => 60],
            ['codigo' => 'ALIM001', 'nombre' => 'Café Premium 250g', 'categoria_id' => 3, 'precio_compra' => 25, 'precio_venta' => 45, 'stock' => 40],
            ['codigo' => 'HOGA001', 'nombre' => 'Lámpara LED', 'categoria_id' => 4, 'precio_compra' => 30, 'precio_venta' => 55, 'stock' => 35],
        ];

        foreach ($productos as $prod) {
            Producto::create($prod);
        }
    }
}
