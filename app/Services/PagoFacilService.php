<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PagoFacilService
{
    protected $baseUrl;
    protected $apiUrl;
    protected $tcTokenService;
    protected $tcTokenSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.pagofacil.base_url', 'https://masterqr.pagofacil.com.bo');
        $this->apiUrl = config('services.pagofacil.api_url', 'https://masterqr.pagofacil.com.bo/api/services/v2');
        $this->tcTokenService = config('services.pagofacil.tc_token_service');
        $this->tcTokenSecret = config('services.pagofacil.tc_token_secret');
    }

    /**
     * Autenticar y obtener Bearer token
     */
    protected function obtenerBearerToken(): string
    {
        // Verificar si hay un token en caché (válido por 1 hora)
        $tokenCacheKey = 'pagofacil_bearer_token';
        $cachedToken = Cache::get($tokenCacheKey);

        if ($cachedToken) {
            Log::info('🔑 [PagoFácil] Usando token en caché');
            return $cachedToken;
        }

        if (!$this->tcTokenService || !$this->tcTokenSecret) {
            throw new \Exception('Las credenciales de PagoFácil no están configuradas. Verifica PAGOFACIL_TC_TOKEN_SERVICE y PAGOFACIL_TC_TOKEN_SECRET en .env');
        }

        try {
            Log::info('🔐 [PagoFácil] Autenticando para obtener Bearer token');

            // Endpoint correcto de autenticación
            $endpoint = "{$this->apiUrl}/login";

            Log::info("🔍 [PagoFácil] Intentando autenticación en: {$endpoint}");

            // Las credenciales van en el Header, no en el body
            $response = Http::timeout(10)
                ->withHeaders([
                    'tcTokenService' => $this->tcTokenService,
                    'tcTokenSecret' => $this->tcTokenSecret,
                ])
                ->post($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                // El token está en values.accessToken según la respuesta de PagoFácil
                $token = $data['values']['accessToken'] ?? $data['accessToken'] ?? $data['token'] ?? $data['access_token'] ?? $data['data']['token'] ?? null;

                if ($token) {
                    // Guardar en caché por 1 hora
                    Cache::put($tokenCacheKey, $token, now()->addHour());
                    Log::info('✅ [PagoFácil] Token obtenido exitosamente');
                    return $token;
                }

                throw new \Exception('No se encontró el token en la respuesta: ' . json_encode($data));
            }

            throw new \Exception("Error al autenticar. Status {$response->status()}: {$response->body()}");
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al autenticar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtener headers con autenticación
     */
    protected function obtenerHeaders(): array
    {
        $token = $this->obtenerBearerToken();

        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    /**
     * Generar QR para pago
     */
    public function generateQr(array $datos): array
    {
        try {
            Log::info('🌐 [PagoFácil] Generando QR', ['datos' => $datos]);

            $headers = $this->obtenerHeaders();

            $response = Http::withHeaders($headers)
                ->post("{$this->apiUrl}/generate-qr", $datos);

            Log::info('📥 [PagoFácil] Respuesta recibida', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ [PagoFácil] Respuesta exitosa de generate-qr', ['data' => $data]);

                // La respuesta puede estar en values según la estructura de PagoFácil
                $responseData = $data['values'] ?? $data;

                $result = [
                    'transactionId' => $responseData['transactionId'] ?? $responseData['transaction_id'] ?? null,
                    'qrBase64' => $responseData['qrBase64'] ?? $responseData['qr_base64'] ?? null,
                    'expirationDate' => $responseData['expirationDate'] ?? $responseData['expiration_date'] ?? null,
                ];

                Log::info('📊 [PagoFácil] Datos extraídos del QR', ['result' => $result]);

                return $result;
            }

            throw new \Exception('Error al generar QR: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al generar QR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Consultar estado de transacción
     */
    /**
     * Consultar estado de transacción
     * 
     * @param string $transactionId ID de transacción de PagoFácil (pagofacilTransactionId)
     * @param string|null $companyTransactionId ID de transacción de la empresa (opcional)
     */
    public function consultarTransaccion(string $transactionId): array
    {
        try {
            Log::info('🔍 [PagoFácil] Consultando transacción', [
                'pagofacil_transaction_id' => $transactionId,

            ]);

            $headers = $this->obtenerHeaders();

            // Preparar body según documentación: solo se requiere uno de los dos
            $body = [];
            if ($transactionId) {
                $body['pagofacilTransactionId'] = $transactionId;
            }


            Log::info("📤 [PagoFácil] Enviando consulta", [
                'endpoint' => "{$this->apiUrl}/query-transaction",
                'body' => $body
            ]);

            $response = Http::withHeaders($headers)
                ->timeout(60) // o el número de segundos que necesites
                ->post("{$this->apiUrl}/query-transaction", $body);

            Log::info("📥 [PagoFácil] Respuesta recibida", [
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            // Reemplazar: Console.Log($response);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ [PagoFácil] Consulta exitosa', ['data' => $data]);
                return $data;
            }

            throw new \Exception('Error al consultar transacción: Status ' . $response->status() . ' - ' . $response->body());
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al consultar transacción', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Procesar pago con tarjeta
     */
    public function procesarTarjeta(array $datos): array
    {
        try {
            Log::info('💳 [PagoFácil] Procesando pago con tarjeta', ['datos' => array_merge($datos, ['cardNumber' => '****', 'cvv' => '***'])]);

            $headers = $this->obtenerHeaders();

            $response = Http::withHeaders($headers)
                ->post("{$this->apiUrl}/card/process", $datos);

            Log::info('📥 [PagoFácil] Respuesta tarjeta recibida', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new \Exception('Error al procesar tarjeta: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al procesar tarjeta', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
