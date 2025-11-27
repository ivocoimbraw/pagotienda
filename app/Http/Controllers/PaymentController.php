<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use App\Services\PagoFacilService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function __construct(
        private PagoFacilService $pagoFacilService
    ) {}

    /**
     * Crear una nueva transacción y generar QR
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'nombre_cliente' => 'required|string|max:255',
            'email' => 'required|email',
            'telefono' => 'required|string|max:20',
            'monto' => 'required|numeric|min:0.1',
            'detalle_pedido' => 'required|array',
        ], [
            'nombre_cliente.required' => 'El nombre del cliente es obligatorio.',
            'nombre_cliente.string' => 'El nombre del cliente debe ser texto.',
            'nombre_cliente.max' => 'El nombre del cliente no debe exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.string' => 'El número de teléfono debe ser texto.',
            'telefono.max' => 'El número de teléfono no debe exceder los 20 caracteres.',
            'monto.required' => 'El monto es obligatorio.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser al menos 0.1.',
            'detalle_pedido.required' => 'El detalle del pedido es obligatorio.',
            'detalle_pedido.array' => 'El detalle del pedido debe ser un arreglo.',
        ]);

        // Generar número de pedido único
        $numeroPedido = 'ORD-' . time() . '-' . rand(1000, 9999);

        // Crear transacción local
        $transaccion = Transaccion::create([
            'numero_pedido' => $numeroPedido,
            'nombre_cliente' => $validated['nombre_cliente'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'monto' => $validated['monto'],
            'estado' => Transaccion::ESTADO_PENDIENTE,
            'detalle_pedido' => $validated['detalle_pedido'],
        ]);

        // Generar QR con PagoFácil
        $qrData = $this->pagoFacilService->generarQr([
            'nombre_cliente' => $validated['nombre_cliente'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'],
            'monto' => $validated['monto'],
            'numero_pedido' => $numeroPedido,
            'detalle_pedido' => $validated['detalle_pedido'],
        ]);

        if (!$qrData) {
            $transaccion->update(['estado' => Transaccion::ESTADO_ERROR]);
            return response()->json([
                'error' => true,
                'message' => 'Error al generar QR'
            ], 500);
        }

        // Actualizar transacción con datos de PagoFácil
        $transaccion->update([
            'id_transaccion' => $qrData['transactionId'],
            'qr_base64' => $qrData['qrBase64'],
            'url_checkout' => $qrData['checkoutUrl'] ?? null,
            'fecha_expiracion' => $qrData['expirationDate'],
        ]);

        return response()->json([
            'success' => true,
            'transaccion' => $transaccion,
        ]);
    }

    /**
     * Consultar estado de transacción
     */
    public function status($id)
    {
        $transaccion = Transaccion::findOrFail($id);

        // Si ya está pagado, retornar estado actual
        if ($transaccion->estado === Transaccion::ESTADO_PAGADO) {
            return response()->json([
                'success' => true,
                'status' => 'paid',
                'transaccion' => $transaccion,
            ]);
        }

        // Consultar estado en PagoFácil
        if ($transaccion->id_transaccion) {
            $status = $this->pagoFacilService->consultarTransaccion($transaccion->id_transaccion);
            
            if ($status && isset($status['paymentStatus'])) {
                // Actualizar estado si está pagado
                if ($status['paymentStatus'] === 2) {
                    $transaccion->update([
                        'estado' => Transaccion::ESTADO_PAGADO,
                        'fecha_pago' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $transaccion->estado === Transaccion::ESTADO_PAGADO ? 'paid' : 'pending',
            'transaccion' => $transaccion->fresh(),
        ]);
    }

    /**
     * Callback de PagoFácil
     */
    public function callback(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Callback recibido de PagoFácil', $data);
            
            $pedidoID = $data['PedidoID'] ?? null;
            $estado = $data['Estado'] ?? null;

            // Buscar transacción por número de pedido
            $transaccion = Transaccion::where('numero_pedido', $pedidoID)->first();

            if (!$transaccion) {
                return response()->json([
                    'error' => 1,
                    'status' => 0,
                    'message' => 'Transacción no encontrada',
                    'messageMostrar' => 0,
                    'messageSistema' => '',
                    'values' => false
                ]);
            }

            // Verificar estado (2 = Pagado)
            if ($estado == 2) {
                $transaccion->update([
                    'estado' => Transaccion::ESTADO_PAGADO,
                    'fecha_pago' => now(),
                ]);
            }

            return response()->json([
                'error' => 0,
                'status' => 1,
                'message' => 'Pago recibido correctamente',
                'messageMostrar' => 0,
                'messageSistema' => '',
                'values' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en callback PagoFácil: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'status' => 0,
                'message' => 'Error al procesar',
                'messageMostrar' => 0,
                'messageSistema' => $e->getMessage(),
                'values' => false
            ]);
        }
    }

    /**
     * Mostrar vista de pago
     */
    public function show()
    {
        return Inertia::render('Payment');
    }
}
