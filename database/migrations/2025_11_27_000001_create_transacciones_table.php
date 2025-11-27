<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transacciones', function (Blueprint $table) {
            $table->id();
            $table->string('id_transaccion')->unique()->nullable(); // PagoFacil transaction ID
            $table->string('numero_pedido')->unique(); // Numero de pedido/orden
            $table->string('nombre_cliente');
            $table->string('email');
            $table->string('telefono');
            $table->decimal('monto', 10, 2);
            $table->integer('estado')->default(1); // 1=pendiente, 2=pagado, 3=expirado, 4=error
            $table->text('qr_base64')->nullable();
            $table->string('url_checkout')->nullable();
            $table->timestamp('fecha_expiracion')->nullable();
            $table->timestamp('fecha_pago')->nullable();
            $table->json('detalle_pedido')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transacciones');
    }
};
