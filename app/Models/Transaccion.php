<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaccion extends Model
{
    use HasFactory;

    protected $table = 'transacciones';

    protected $fillable = [
        'id_transaccion',
        'numero_pedido',
        'nombre_cliente',
        'email',
        'telefono',
        'monto',
        'estado',
        'qr_base64',
        'url_checkout',
        'fecha_expiracion',
        'fecha_pago',
        'detalle_pedido',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_expiracion' => 'datetime',
        'fecha_pago' => 'datetime',
        'detalle_pedido' => 'array',
    ];

    // Constantes de estado
    const ESTADO_PENDIENTE = 1;
    const ESTADO_PAGADO = 2;
    const ESTADO_EXPIRADO = 3;
    const ESTADO_ERROR = 4;
}
