<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo', ['efectivo', 'qr', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->string('referencia')->nullable();
            $table->string('comprobante')->nullable();
            $table->enum('estado', ['pendiente', 'completado', 'rechazado'])->default('pendiente');
            $table->date('fecha_pago');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
