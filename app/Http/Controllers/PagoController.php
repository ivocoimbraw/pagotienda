<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PagoController extends Controller
{
    public function __construct(
        private PagoFacilService $pagoFacilService
    ) {}

    public function store(Request $request, Venta $venta)
    {
        $validated = $request->validate([
            'monto' => 'required|numeric|min:0.1|max:' . $venta->saldoPendiente(),
            'metodo' => 'required|in:efectivo,qr,transferencia',
            'referencia' => 'nullable|string',
        ]);

        $pago = Pago::create([
            'venta_id' => $venta->id,
            'monto' => $validated['monto'],
            'metodo' => $validated['metodo'],
            'referencia' => $validated['referencia'],
            'estado' => $validated['metodo'] === 'efectivo' ? Pago::ESTADO_COMPLETADO : Pago::ESTADO_PENDIENTE,
            'fecha_pago' => now(),
        ]);

        if ($validated['metodo'] === 'efectivo') {
            $this->verificarPagoCompleto($venta);
        }

        return back()->with('success', 'Pago registrado correctamente');
    }

    public function generarQr(Request $request, Venta $venta)
    {
        $monto = $request->input('monto', $venta->saldoPendiente());

        try {
            $resultado = $this->pagoFacilService->generarQR($monto, $venta->numero_venta);

            if ($resultado['success']) {
                $pago = Pago::create([
                    'venta_id' => $venta->id,
                    'monto' => $monto,
                    'metodo_pago' => 'qr',
                    'id_transaccion_pf' => $resultado['id_transaccion'],
                    'estado' => Pago::ESTADO_PENDIENTE,
                ]);

                return response()->json([
                    'success' => true,
                    'qr_image' => $resultado['qr_image'],
                    'pago_id' => $pago->id,
                ]);
            }

            return response()->json(['error' => 'No se pudo generar el QR'], 500);
        } catch (\Exception $e) {
            Log::error('Error generando QR: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function callback(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Callback PagoFácil recibido', $data);

            $pedidoID = $data['PedidoID'] ?? null;
            $estado = $data['Estado'] ?? null;

            $venta = Venta::where('numero_venta', $pedidoID)->first();

            if (!$venta) {
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Venta no encontrada',
                    'values' => false
                ]);
            }

            if ($estado == 2) {
                $pago = $venta->pagos()->where('metodo', 'qr')->where('estado', Pago::ESTADO_PENDIENTE)->first();
                if ($pago) {
                    $pago->update([
                        'estado' => Pago::ESTADO_COMPLETADO,
                    ]);
                }
                $this->verificarPagoCompleto($venta);
            }

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Pago procesado correctamente',
                'values' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error al procesar',
                'values' => false
            ]);
        }
    }

    private function verificarPagoCompleto(Venta $venta): void
    {
        $venta->refresh();
        if ($venta->saldoPendiente() <= 0) {
            $venta->update(['estado' => Venta::ESTADO_PAGADO]);
        }
    }
}
