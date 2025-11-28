<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Callback de PagoFácil (sin auth)
Route::post('/pagofacil/callback', [PagoController::class, 'callback'])->name('pagofacil.callback');

// Rutas protegidas
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Categorías
    Route::resource('categorias', CategoriaController::class)->except(['show', 'create', 'edit']);

    // Productos
    Route::resource('productos', ProductoController::class)->except(['show']);

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');

    // Pagos
    Route::post('/ventas/{venta}/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::post('/ventas/{venta}/generar-qr', [PagoController::class, 'generarQr'])->name('pagos.generar-qr');
});
