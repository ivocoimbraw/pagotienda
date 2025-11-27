<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PagoFacilService
{
    private string $baseUrl;
    private string $tokenService;
    private string $tokenSecret;
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->tokenService = config('services.pagofacil.token_service');
        $this->tokenSecret = config('services.pagofacil.token_secret');
    }

    /**
     * Autenticar y obtener access token
     */
    public function autenticar(): ?string
    {
        try {
            $response = Http::timeout(5)->withHeaders([
                'tcTokenService' => $this->tokenService,
                'tcTokenSecret' => $this->tokenSecret,
            ])->post("{$this->baseUrl}/login");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['error'] === 0 && isset($data['values']['accessToken'])) {
                    $this->accessToken = $data['values']['accessToken'];
                    return $this->accessToken;
                }
            }

            Log::error('PagoFacil authentication failed', ['response' => $response->json()]);
            return null;
        } catch (Exception $e) {
            Log::error('PagoFacil authentication error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generar QR de pago
     */
    public function generarQr(array $data): ?array
    {
        if (!$this->accessToken) {
            if (!$this->autenticar()) {
                throw new Exception('No se pudo autenticar con PagoFácil');
            }
        }

        try {
            // Usar APP_URL para callback (permite ngrok en desarrollo)
            $callbackUrl = rtrim(config('app.url'), '/') . '/pagofacil/callback';
            
            $response = Http::timeout(10)->withToken($this->accessToken)
                ->post("{$this->baseUrl}/generate-qr", [
                    'paymentMethod' => 4, // QR
                    'clientName' => $data['nombre_cliente'],
                    'documentType' => 1,
                    'documentId' => $data['document_id'] ?? '0',
                    'phoneNumber' => $data['telefono'],
                    'email' => $data['email'],
                    'paymentNumber' => $data['numero_pedido'],
                    'amount' => $data['monto'],
                    'currency' => 2, // BOB
                    'clientCode' => $data['client_code'] ?? '00001',
                    'callbackUrl' => $callbackUrl,
                    'orderDetail' => $data['detalle_pedido'],
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['error'] === 0) {
                    return $result['values'];
                }
            }

            Log::error('PagoFacil QR generation failed', ['response' => $response->json()]);
            return null;
        } catch (Exception $e) {
            Log::error('PagoFacil QR generation error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Consultar estado de transacción
     */
    public function consultarTransaccion(string $idTransaccion): ?array
    {
        if (!$this->accessToken) {
            if (!$this->autenticar()) {
                throw new Exception('No se pudo autenticar con PagoFácil');
            }
        }

        try {
            $response = Http::timeout(5)->withToken($this->accessToken)
                ->post("{$this->baseUrl}/query-transaction", [
                    'pagofacilTransactionId' => $idTransaccion,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                if ($result['error'] === 0) {
                    return $result['values'];
                }
            }

            return null;
        } catch (Exception $e) {
            Log::error('PagoFacil query transaction error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
