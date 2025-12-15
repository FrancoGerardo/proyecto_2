<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ficha;
use App\Models\Pago;
use App\Models\Cliente;
use App\Services\PagoFacilService;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PagoClienteController extends Controller
{
    protected $pagoFacilService;

    public function __construct(PagoFacilService $pagoFacilService)
    {
        $this->pagoFacilService = $pagoFacilService;
    }

    /**
     * Mostrar pantalla de pago
     */
    public function procesarPago(string $fichaId)
    {
        $ficha = Ficha::with(['servicio', 'medico.usuario.persona', 'cliente.usuario.persona'])
            ->findOrFail($fichaId);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->route('cliente.fichas.index')
                ->with('error', 'No tienes permiso para ver esta ficha.');
        }

        // Calcular saldo pendiente
        $totalPagado = $ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
        $totalAPagar = $ficha->servicio->costo ?? 0;
        $saldoPendiente = $totalAPagar - $totalPagado;

        // Verificar si ya hay un pago pendiente con QR
        $pagoPendiente = $ficha->pagos()
            ->where('metodo_pago', 'QR')
            ->where('qr_status', 'PENDING')
            ->where('estado', 'PENDIENTE')
            ->first();

        return Inertia::render('PagosCliente/Procesar', [
            'ficha' => $ficha,
            'saldoPendiente' => $saldoPendiente,
            'totalPagado' => $totalPagado,
            'pagoPendiente' => $pagoPendiente,
        ]);
    }

    /**
     * Generar QR para pago
     */
    public function generarQr(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
        ]);

        $ficha = Ficha::with(['servicio', 'cliente.usuario.persona'])->findOrFail($request->ficha_id);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para esta ficha.']);
        }

        // Calcular saldo pendiente
        $totalPagado = $ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
        $totalAPagar = $ficha->servicio->costo ?? 0;
        $saldoPendiente = $totalAPagar - $totalPagado;

        if ($saldoPendiente <= 0) {
            return redirect()->back()->withErrors(['error' => 'Esta ficha ya está completamente pagada.']);
        }

        try {
            // Generar ID de transacción único
            $companyTransactionId = 'FICHA-' . $ficha->id . '-' . time();

            Log::info('🔑 [PagoFácil] ID de transacción generado', ['company_transaction_id' => $companyTransactionId]);

            // Preparar datos para PagoFácil
            $cliente = $ficha->cliente->usuario->persona;
            $qrData = [
                'paymentMethod' => 4, // QR Simple
                'clientName' => $cliente->nombre_completo ?? 'Cliente',
                'documentType' => 1, // CI
                'documentId' => $cliente->dni ?? '00000000',
                'phoneNumber' => $cliente->telefono ?? '70000000',
                'email' => $ficha->cliente->usuario->email ?? '',
                'paymentNumber' => $companyTransactionId,
                'amount' => (float) $saldoPendiente,
                'currency' => 2, // BOB
                'clientCode' => (string) $ficha->cliente_id,
                'callbackUrl' => config('app.url') . '/cliente/pagos/callback',
                'orderDetail' => [
                    [
                        'serial' => 1,
                        'product' => "Ficha #{$ficha->id} - {$ficha->servicio->nombre}",
                        'quantity' => 1,
                        'price' => (float) $saldoPendiente,
                        'discount' => 0,
                        'total' => (float) $saldoPendiente,
                    ]
                ]
            ];

            Log::info('📋 [PagoFácil] Datos preparados', ['qr_data' => $qrData]);

            // Generar QR
            Log::info('🚀 [PagoFácil] Llamando a generateQr...');
            $response = $this->pagoFacilService->generateQr($qrData);

            Log::info('✅ [PagoFácil] QR generado exitosamente', [
                'response' => $response,
                'tiene_transactionId' => isset($response['transactionId']),
                'tiene_qrBase64' => isset($response['qrBase64']),
                'tiene_expirationDate' => isset($response['expirationDate']),
            ]);

            // Guardar el pago pendiente con el QR
            $pagoId = Str::uuid()->toString();
            $pago = Pago::create([
                'id' => $pagoId,
                'ficha_id' => $ficha->id,
                'monto' => $saldoPendiente,
                'tipo' => 'CONTADO',
                'metodo_pago' => 'QR',
                'fecha_pago' => now(),
                'estado' => 'PENDIENTE',
                'pagofacil_transaction_id' => $response['transactionId'],
                'company_transaction_id' => $companyTransactionId,
                'qr_base64' => $response['qrBase64'],
                'qr_status' => 'PENDING',
                'qr_expiration' => $response['expirationDate'] ? \Carbon\Carbon::parse($response['expirationDate']) : null,
            ]);

            Log::info('💾 [PagoFácil] Pago guardado', ['pago_id' => $pago->id]);

            $qrDataParaFlash = [
                'qrBase64' => $response['qrBase64'] ?? null,
                'transactionId' => $response['transactionId'] ?? null,
                'expirationDate' => $response['expirationDate'] ?? null,
            ];

            Log::info('📤 [PagoFácil] Redirigiendo con datos QR', ['qr_data' => $qrDataParaFlash]);

            // Guardar en sesión flash para que Inertia lo capture
            session()->flash('success', 'QR generado exitosamente.');
            session()->flash('qr_data', $qrDataParaFlash);

            // Redirigir a la página de procesar pago
            return redirect()->route('cliente.pagos.procesar', $ficha->id);

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al generar QR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al generar QR: ' . $e->getMessage()]);
        }
    }

    /**
     * Procesar pago con tarjeta
     */
    public function procesarTarjeta(Request $request)
    {
        $request->validate([
            'ficha_id' => 'required|string|exists:fichas,id',
            'numero_tarjeta' => 'required|string|min:13|max:19',
            'nombre_titular' => 'required|string|max:100',
            'fecha_vencimiento' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required|string|min:3|max:4',
        ]);

        $ficha = Ficha::with(['servicio', 'cliente.usuario.persona'])->findOrFail($request->ficha_id);

        // Verificar que la ficha pertenece al usuario autenticado
        if ($ficha->cliente_id !== auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No tienes permiso para esta ficha.']);
        }

        // Calcular saldo pendiente
        $totalPagado = $ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
        $totalAPagar = $ficha->servicio->costo ?? 0;
        $saldoPendiente = $totalAPagar - $totalPagado;

        if ($saldoPendiente <= 0) {
            return redirect()->back()->withErrors(['error' => 'Esta ficha ya está completamente pagada.']);
        }

        try {
            $companyTransactionId = 'FICHA-' . $ficha->id . '-' . time();

            // Preparar datos para PagoFácil
            $cliente = $ficha->cliente->usuario->persona;
            $tarjetaData = [
                'paymentMethod' => 1, // Tarjeta
                'cardNumber' => $request->numero_tarjeta,
                'cardHolderName' => $request->nombre_titular,
                'expirationDate' => $request->fecha_vencimiento,
                'cvv' => $request->cvv,
                'amount' => (float) $saldoPendiente,
                'currency' => 2, // BOB
                'paymentNumber' => $companyTransactionId,
                'clientName' => $cliente->nombre_completo ?? 'Cliente',
                'clientCode' => (string) $ficha->cliente_id,
            ];

            Log::info('💳 [PagoFácil] Procesando tarjeta', ['datos' => array_merge($tarjetaData, ['cardNumber' => '****', 'cvv' => '***'])]);

            // Procesar pago con tarjeta
            $response = $this->pagoFacilService->procesarTarjeta($tarjetaData);

            Log::info('✅ [PagoFácil] Tarjeta procesada', ['response' => $response]);

            // Guardar el pago
            $pagoId = Str::uuid()->toString();
            $pago = Pago::create([
                'id' => $pagoId,
                'ficha_id' => $ficha->id,
                'monto' => $saldoPendiente,
                'tipo' => 'CONTADO',
                'metodo_pago' => 'TARJETA',
                'fecha_pago' => now(),
                'estado' => isset($response['status']) && $response['status'] == 2 ? 'PAGADO' : 'PENDIENTE',
                'pagofacil_transaction_id' => $response['transactionId'] ?? null,
                'company_transaction_id' => $companyTransactionId,
            ]);

            // Si el pago fue exitoso, actualizar estado de la ficha
            if ($pago->estado === 'PAGADO') {
                $ficha->update(['estado' => 'CONFIRMADA']);
            }

            return redirect()->route('cliente.fichas.index')
                ->with('success', 'Pago procesado exitosamente.');

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al procesar tarjeta', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->withErrors(['error' => 'Error al procesar pago: ' . $e->getMessage()]);
        }
    }

    /**
     * Callback de PagoFácil (Webhook)
     */
    public function callback(Request $request)
    {
        Log::info('📞 [PagoFácil] Callback recibido', [
            'all_data' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
        ]);

        // Capturar todos los campos según documentación oficial
        $pedidoId = $request->input('PedidoID') ?? $request->input('PedidoId') ?? $request->input('pedidoId') ?? $request->input('pedido_id');
        $estado = $request->input('Estado') ?? $request->input('estado') ?? $request->input('status') ?? $request->input('Status');
        $fecha = $request->input('Fecha') ?? $request->input('fecha');
        $hora = $request->input('Hora') ?? $request->input('hora');
        $metodoPago = $request->input('MetodoPago') ?? $request->input('metodoPago') ?? $request->input('metodo_pago');

        Log::info('🔍 [PagoFácil] Datos extraídos del callback (según documentación)', [
            'PedidoID' => $pedidoId,
            'Estado' => $estado,
            'Fecha' => $fecha,
            'Hora' => $hora,
            'MetodoPago' => $metodoPago,
            'todos_los_campos' => $request->all(),
        ]);

        // Validar campo obligatorio según documentación
        if (!$pedidoId) {
            Log::error('❌ [PagoFácil] No se recibió PedidoID en el callback', ['request_data' => $request->all()]);
            return response()->json(['error' => 1, 'message' => 'PedidoID no encontrado'], 400);
        }

        // Buscar el pago por company_transaction_id
        $pago = Pago::where('company_transaction_id', $pedidoId)->first();

        if (!$pago) {
            Log::warning('⚠️ [PagoFácil] Pago no encontrado', [
                'company_transaction_id' => $pedidoId,
                'buscando_en' => 'company_transaction_id'
            ]);
            return response()->json(['error' => 1, 'message' => 'Pago no encontrado'], 404);
        }

        Log::info('📋 [PagoFácil] Pago encontrado', [
            'pago_id' => $pago->id,
            'estado_actual' => $pago->estado,
            'qr_status_actual' => $pago->qr_status,
            'ficha_id' => $pago->ficha_id,
        ]);

        // Actualizar el estado del pago si fue exitoso
        // Verificar múltiples formatos de estado que PagoFácil podría enviar
        $estadoNormalizado = is_numeric($estado) ? (int)$estado : strtoupper(trim((string)$estado));
        $esPagado = in_array($estadoNormalizado, ['COMPLETADO', 'PAID', 'PAGADO', 2, '2', 'COMPLETED']) 
                 || $estado === 'Completado' 
                 || $estado === 'PAID';

        Log::info('💳 [PagoFácil] Verificando estado del pago', [
            'estado_recibido' => $estado,
            'estado_normalizado' => $estadoNormalizado,
            'es_pagado' => $esPagado,
        ]);

        if ($esPagado) {
            Log::info('✅ [PagoFácil] ¡PAGO CONFIRMADO EN CALLBACK! Actualizando...', [
                'pago_id' => $pago->id,
                'estado_anterior' => $pago->estado,
                'qr_status_anterior' => $pago->qr_status,
                'fecha_callback' => $fecha,
                'hora_callback' => $hora,
                'metodo_pago_callback' => $metodoPago,
            ]);

            // Construir fecha_pago usando Fecha y Hora del callback (según documentación)
            $fechaPago = null;
            if ($fecha && $hora) {
                try {
                    // Intentar parsear fecha y hora del callback
                    // Formato esperado: "Y-m-d" para fecha y "H:i:s" para hora
                    $fechaPago = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $fecha . ' ' . $hora);
                    Log::info('📅 [PagoFácil] Fecha y hora parseadas del callback', [
                        'fecha_original' => $fecha,
                        'hora_original' => $hora,
                        'fecha_pago_parseada' => $fechaPago->toDateTimeString(),
                    ]);
                } catch (\Exception $e) {
                    Log::warning('⚠️ [PagoFácil] Error al parsear fecha/hora del callback, usando now()', [
                        'error' => $e->getMessage(),
                        'fecha' => $fecha,
                        'hora' => $hora,
                    ]);
                    $fechaPago = now();
                }
            } else {
                // Si no vienen fecha/hora del callback, usar now()
                Log::info('ℹ️ [PagoFácil] No se recibieron Fecha/Hora del callback, usando now()');
                $fechaPago = now();
            }

            $pago->update([
                'qr_status' => 'PAID',
                'estado' => 'PAGADO',
                'fecha_pago' => $fechaPago,
            ]);

            // Refrescar para obtener los nuevos valores
            $pago->refresh();

            Log::info('💾 [PagoFácil] Pago actualizado en BD', [
                'pago_id' => $pago->id,
                'nuevo_estado' => $pago->estado,
                'nuevo_qr_status' => $pago->qr_status,
                'nueva_fecha_pago' => $pago->fecha_pago,
                'fecha_usada_del_callback' => $fechaPago->toDateTimeString(),
                'metodo_pago_recibido' => $metodoPago,
            ]);

            // Actualizar estado de la ficha
            if ($pago->ficha) {
                $totalPagado = $pago->ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
                $totalAPagar = $pago->ficha->servicio->costo ?? 0;

                Log::info('📊 [PagoFácil] Verificando totales de ficha', [
                    'totalPagado' => $totalPagado,
                    'totalAPagar' => $totalAPagar,
                    'esta_completo' => $totalPagado >= $totalAPagar,
                ]);

                if ($totalPagado >= $totalAPagar) {
                    $pago->ficha->update(['estado' => 'CONFIRMADA']);
                    Log::info('✅ [PagoFácil] Ficha actualizada a CONFIRMADA', ['ficha_id' => $pago->ficha_id]);
                }
            }

            Log::info('✅ [PagoFácil] Callback procesado exitosamente', ['pago_id' => $pago->id]);
        } else {
            Log::info('ℹ️ [PagoFácil] Estado recibido no es de pago completado', [
                'estado' => $estado,
                'pago_id' => $pago->id,
                'fecha_callback' => $fecha,
                'hora_callback' => $hora,
                'metodo_pago_callback' => $metodoPago,
            ]);
        }

        // Responder conforme a la API de PagoFácil
        return response()->json([
            'error' => 0,
            'status' => 1,
            'message' => 'Notificación recibida correctamente',
            'values' => true
        ], 200);
    }

    /**
     * Consultar estado de transacción
     */
    public function consultarEstado(Request $request)
    {
        // Aceptar transactionId o ficha_id para compatibilidad
        $transactionId = $request->input('transaction_id') ?? $request->input('transactionId');
        $fichaId = $request->input('ficha_id');
        
        if (!$transactionId ) {
            return response()->json(['error' => 'Se requiere transaction_id'], 400);
        }

        // Buscar pago por transactionId directamente (como en Postman)
        if ($transactionId) {
            $pago = Pago::where('pagofacil_transaction_id', $transactionId)
                ->first();
            
            if (!$pago) {
                Log::warning('⚠️ [PagoFácil] No se encontró pago con transactionId', ['transactionId' => $transactionId]);
                return response()->json(['error' => 'Pago no encontrado'], 404);
            }
        }

        // Verificar que el pago pertenece al usuario autenticado
        if ($pago->ficha && $pago->ficha->cliente_id !== auth()->id()) {
            return response()->json(['error' => 'No tienes permiso'], 403);
        }

        // REFRESCAR el modelo para obtener los datos más recientes de la BD
        // Esto es importante porque el callback puede haber actualizado el pago
        $pago->refresh();

        Log::info('🔍 [PagoFácil] Estado actual del pago en BD', [
            'pago_id' => $pago->id,
            'qr_status' => $pago->qr_status,
            'estado' => $pago->estado,
            'pagofacil_transaction_id' => $pago->pagofacil_transaction_id,
            'company_transaction_id' => $pago->company_transaction_id,
            'updated_at' => $pago->updated_at,
        ]);

        // Si el pago ya está PAID, devolver directamente sin consultar API
        if ($pago->qr_status === 'PAID' || $pago->estado === 'PAGADO') {
            Log::info('✅ [PagoFácil] ¡PAGO YA CONFIRMADO EN BD! Devolviendo directamente...', [
                'pago_id' => $pago->id,
                'qr_status' => $pago->qr_status,
                'estado' => $pago->estado,
                'fecha_pago' => $pago->fecha_pago,
            ]);

            return response()->json([
                'success' => true,
                'status' => 'PAID',
                'message' => '✅ Pago confirmado exitosamente',
            ]);
        }

        try {
            Log::info('🔍 [PagoFácil] Consultando estado de transacción', [
                'pagofacil_transaction_id' => $pago->pagofacil_transaction_id,
                'company_transaction_id' => $pago->company_transaction_id,
                'pago_id' => $pago->id,
                'qr_status_actual' => $pago->qr_status,
                'estado_actual' => $pago->estado,
                'ficha_id' => $pago->ficha_id
            ]);

            // Consultar usando el ID de PagoFácil o el ID de la empresa
            $result = $this->pagoFacilService->consultarTransaccion(
                $pago->pagofacil_transaction_id,
                $pago->company_transaction_id
            );

            Log::info('📥 [PagoFácil] Respuesta de consulta recibida', [
                'result' => $result,
                'result_keys' => array_keys($result ?? []),
                'tiene_values' => isset($result['values']),
                'error' => $result['error'] ?? null,
                'status_api' => $result['status'] ?? null,
                'message' => $result['message'] ?? null,
            ]);

            // Verificar que la respuesta de la API sea exitosa
            $errorCode = $result['error'] ?? null;
            $apiStatus = $result['status'] ?? null;
            
            if ($errorCode !== 0 && $errorCode !== null) {
                Log::warning('⚠️ [PagoFácil] La API reportó un error', [
                    'error' => $errorCode,
                    'status' => $apiStatus,
                    'message' => $result['message'] ?? null
                ]);
            }

            // La información del pago está en 'values'
            $responseData = $result['values'] ?? [];
            
            if (empty($responseData)) {
                Log::warning('⚠️ [PagoFácil] No se encontró "values" en la respuesta', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'status' => 'ERROR',
                    'message' => 'Respuesta inválida de la API',
                ], 500);
            }

            // Obtener el estado del pago desde values
            $paymentStatus = $responseData['paymentStatus'] ?? null;
            $paymentStatusDescription = $responseData['paymentStatusDescription'] ?? null;

            Log::info('🔍 [PagoFácil] Analizando respuesta', [
                'error_code' => $errorCode,
                'api_status' => $apiStatus,
                'responseData' => $responseData,
                'paymentStatus' => $paymentStatus,
                'paymentStatusDescription' => $paymentStatusDescription,
                'responseData_keys' => array_keys($responseData ?? []),
            ]);

            // Verificar que tenemos el paymentStatus
            if ($paymentStatus === null) {
                Log::warning('⚠️ [PagoFácil] No se encontró paymentStatus en la respuesta', [
                    'responseData' => $responseData
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'ERROR',
                    'message' => 'No se pudo determinar el estado del pago',
                ], 500);
            }

            $status = (int)$paymentStatus; // Asegurar que sea número
            
            Log::info('💳 [PagoFácil] Estado detectado', [
                'status' => $status,
                'status_type' => gettype($status),
                'status_description' => $paymentStatusDescription,
                // Estados conocidos: 1=PENDING, 2=PAID, 3=CANCELLED, 4=EXPIRED, 5=REVISION
            ]);
            
            // Verificar si está PAID (estado 2) o si la descripción indica pago completado
            $esPagado = ($status == 5) 
                || ($paymentStatusDescription && in_array(strtoupper($paymentStatusDescription), ['PAID', 'PAGADO', 'COMPLETADO', 'COMPLETED', 'APROBADO']));
            
            if ($esPagado) { // PAID
                Log::info('✅ [PagoFácil] ¡PAGO CONFIRMADO! Actualizando estado...', [
                    'pago_id' => $pago->id,
                    'status_recibido' => $status
                ]);

                $pago->update([
                    'qr_status' => 'PAID',
                    'estado' => 'PAGADO',
                    'fecha_pago' => now(),
                ]);

                Log::info('💾 [PagoFácil] Pago actualizado en BD', [
                    'pago_id' => $pago->id,
                    'nuevo_qr_status' => $pago->qr_status,
                    'nuevo_estado' => $pago->estado
                ]);

                // Actualizar ficha
                if ($pago->ficha) {
                    $totalPagado = $pago->ficha->pagos()->where('estado', 'PAGADO')->sum('monto');
                    $totalAPagar = $pago->ficha->servicio->costo ?? 0;

                    Log::info('📊 [PagoFácil] Verificando totales de ficha', [
                        'totalPagado' => $totalPagado,
                        'totalAPagar' => $totalAPagar,
                        'esta_completo' => $totalPagado >= $totalAPagar
                    ]);

                    if ($totalPagado >= $totalAPagar) {
                        $pago->ficha->update(['estado' => 'CONFIRMADA']);
                        Log::info('✅ [PagoFácil] Ficha actualizada a CONFIRMADA', ['ficha_id' => $pago->ficha_id]);
                    }
                }

                Log::info('📤 [PagoFácil] Enviando respuesta PAID al frontend');

                return response()->json([
                    'success' => true,
                    'status' => 'PAID',
                    'message' => '✅ Pago confirmado exitosamente',
                ]);
            } elseif ($status == 1) { // PENDING
                return response()->json([
                    'success' => false,
                    'status' => 'PENDING',
                    'message' => '⏳ Pago pendiente de confirmación',
                ]);
            } elseif ($status == 2) { // REVISION (Revisión)
                Log::info('🔍 [PagoFácil] Pago en revisión', [
                    'pago_id' => $pago->id,
                    'status_description' => $paymentStatusDescription
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'REVISION',
                    'message' => '⏳ Pago en revisión. Se confirmará automáticamente cuando se complete.',
                ]);
            } elseif ($status == 3) { // CANCELLED
                $pago->update(['qr_status' => 'CANCELLED', 'estado' => 'ANULADO']);
                return response()->json([
                    'success' => false,
                    'status' => 'CANCELLED',
                    'message' => '❌ Pago cancelado',
                ]);
            } elseif ($status == 4) { // EXPIRED
                $pago->update(['qr_status' => 'EXPIRED']);
                return response()->json([
                    'success' => false,
                    'status' => 'EXPIRED',
                    'message' => '⏰ QR expirado',
                ]);
            } else {
                // Estado desconocido
                Log::warning('⚠️ [PagoFácil] Estado desconocido', [
                    'status' => $status,
                    'status_description' => $paymentStatusDescription
                ]);
                return response()->json([
                    'success' => false,
                    'status' => 'UNKNOWN',
                    'message' => '⏳ Estado desconocido: ' . ($paymentStatusDescription ?? $status),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ [PagoFácil] Error al consultar estado', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Si el error es que no se encontró el endpoint, devolver un mensaje más amigable
            if (str_contains($e->getMessage(), '404') || str_contains($e->getMessage(), 'Not Found')) {
                return response()->json([
                    'success' => false,
                    'status' => 'PENDING',
                    'message' => '⏳ Consulta de estado no disponible. El pago se confirmará automáticamente cuando se complete.',
                ], 200); // 200 para que no se muestre como error en el frontend
            }

            return response()->json([
                'success' => false,
                'status' => 'ERROR',
                'message' => 'Error al consultar estado: ' . $e->getMessage()
            ], 500);
        }
    }
}
