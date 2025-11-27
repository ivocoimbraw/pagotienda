<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Rutas de PagoFácil
Route::get('/pago', [PaymentController::class, 'show'])->name('payment.show');
Route::post('/pago/crear', [PaymentController::class, 'create'])->name('payment.create');
Route::get('/pago/estado/{id}', [PaymentController::class, 'status'])->name('payment.status');
Route::post('/pagofacil/callback', [PaymentController::class, 'callback'])->name('pagofacil.callback');
