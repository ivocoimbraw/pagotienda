<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Pago;
use App\Models\User;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $ventas = Venta::with(['cliente', 'vendedor'])
            ->when($request->buscar, fn($q) => $q->where('numero_venta', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'filtros' => $request->only(['buscar', 'estado']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Ventas/Create', [
            'productos' => Producto::where('activo', true)->where('stock', '>', 0)->with('categoria')->get(),
            'clientes' => User::where('rol', 'cliente')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'nullable|exists:users,id',
            'tipo' => 'required|in:contado,credito',
            'metodo_pago' => 'required|in:efectivo,qr,transferencia',
            'descuento' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
            'detalles' => 'required|array|min:1',
            'detalles.*.producto_id' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.precio_unitario' => 'required|numeric|min:0',
            'detalles.*.descuento' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $subtotal = 0;
            
            foreach ($validated['detalles'] as $item) {
                $itemSubtotal = ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0);
                $subtotal += $itemSubtotal;
            }

            $descuento = $validated['descuento'] ?? 0;
            $total = $subtotal - $descuento;

            $venta = Venta::create([
                'numero_venta' => Venta::generarNumero(),
                'cliente_id' => $validated['cliente_id'],
                'user_id' => $request->user()->id,
                'tipo' => $validated['tipo'],
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'total' => $total,
                'estado' => Venta::ESTADO_PENDIENTE,
                'observaciones' => $validated['observaciones'],
            ]);

            foreach ($validated['detalles'] as $item) {
                $producto = Producto::find($item['producto_id']);
                
                if ($producto->stock < $item['cantidad']) {
                    throw new \Exception("Stock insuficiente para {$producto->nombre}");
                }

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento' => $item['descuento'] ?? 0,
                    'subtotal' => ($item['cantidad'] * $item['precio_unitario']) - ($item['descuento'] ?? 0),
                ]);

                $producto->decrement('stock', $item['cantidad']);
            }

            // Crear pago y procesar según método
            $metodoPago = $validated['metodo_pago'];
            $qrImage = null;

            if ($metodoPago === 'qr') {
                // Generar QR con PagoFácil
                $pagoFacil = new PagoFacilService();
                $resultado = $pagoFacil->generarQr([
                    'nombre_cliente' => $venta->cliente?->name ?? 'Cliente General',
                    'email' => $venta->cliente?->email ?? 'cliente@tiendadame.com',
                    'telefono' => '70000000',
                    'monto' => $total,
                    'numero_pedido' => $venta->numero_venta,
                    'detalle_pedido' => $venta->detalles->map(fn($d) => [
                        'serial' => $d->id,
                        'product' => $d->producto->nombre,
                        'quantity' => $d->cantidad,
                        'price' => $d->precio_unitario,
                        'discount' => $d->descuento,
                        'total' => $d->subtotal,
                    ])->toArray(),
                ]);
                
                if ($resultado) {
                    Pago::create([
                        'venta_id' => $venta->id,
                        'monto' => $total,
                        'metodo' => 'qr',
                        'estado' => Pago::ESTADO_PENDIENTE,
                        'referencia' => $resultado['transactionId'] ?? null,
                        'fecha_pago' => now(),
                    ]);
                    $qrImage = $resultado['qrBase64'] ?? null;
                }
            } elseif ($metodoPago === 'efectivo') {
                // Pago en efectivo: marcar como completado
                Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $total,
                    'metodo' => 'efectivo',
                    'estado' => Pago::ESTADO_COMPLETADO,
                    'fecha_pago' => now(),
                ]);
                $venta->update(['estado' => Venta::ESTADO_PAGADO]);
            } else {
                // Transferencia: pendiente de confirmación
                Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $total,
                    'metodo' => 'transferencia',
                    'estado' => Pago::ESTADO_PENDIENTE,
                    'fecha_pago' => now(),
                ]);
            }

            if ($qrImage) {
                return redirect()->route('ventas.show', $venta)
                    ->with('qrImage', $qrImage);
            }

            return redirect()->route('ventas.show', $venta)
                ->with('success', 'Venta creada correctamente');
        });
    }

    public function show(Venta $venta)
    {
        $venta->load(['cliente', 'vendedor', 'detalles.producto', 'pagos']);

        return Inertia::render('Ventas/Show', [
            'venta' => $venta,
            'qrImage' => session('qrImage'),
        ]);
    }

    public function anular(Venta $venta)
    {
        if ($venta->estado === Venta::ESTADO_CANCELADO) {
            return back()->with('error', 'La venta ya está cancelada');
        }

        DB::transaction(function () use ($venta) {
            foreach ($venta->detalles as $detalle) {
                $detalle->producto->increment('stock', $detalle->cantidad);
            }

            $venta->update(['estado' => Venta::ESTADO_CANCELADO]);
        });

        return back()->with('success', 'Venta cancelada y stock restaurado');
    }
}
