<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $fillable = [
        'venta_id',
        'monto',
        'metodo',
        'referencia',
        'comprobante',
        'estado',
        'fecha_pago',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_RECHAZADO = 'rechazado';

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }
}
