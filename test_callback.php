<?php
/**
 * Script de Prueba - Callback PagoFácil
 * 
 * Uso: php test_callback.php
 * 
 * IMPORTANTE: 
 * - Reemplaza 'FICHA-TEST-12345' con un company_transaction_id real de tu BD
 * - Reemplaza 'http://tu-dominio.com' con tu URL real
 */

// Configuración
$url = 'http://localhost:8000/cliente/pagos/callback'; // Cambia por tu URL
$companyTransactionId = 'FICHA-TEST-12345'; // Cambia por un ID real de tu BD

// Datos del callback según documentación oficial
$data = [
    'PedidoID' => $companyTransactionId,
    'Estado' => 'Completado', // Puede ser: 'Completado', 'PAID', 'Pendiente', etc.
    'Fecha' => date('Y-m-d'), // Formato: Y-m-d
    'Hora' => date('H:i:s'), // Formato: H:i:s
    'MetodoPago' => 'QR'
];

// Configurar cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

echo "🚀 Enviando callback a: $url\n";
echo "📦 Datos enviados:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Ejecutar petición
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Mostrar resultados
echo "📥 Respuesta HTTP: $httpCode\n";
if ($error) {
    echo "❌ Error cURL: $error\n";
} else {
    echo "✅ Respuesta del servidor:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        
        // Verificar respuesta esperada
        if (isset($responseData['error']) && $responseData['error'] == 0) {
            echo "\n✅ ¡Callback procesado exitosamente!\n";
        } else {
            echo "\n⚠️ El callback fue recibido pero hubo un problema:\n";
            echo "   Mensaje: " . ($responseData['message'] ?? 'Sin mensaje') . "\n";
        }
    } else {
        echo $response . "\n";
    }
}

echo "\n";
echo "💡 Siguiente paso: Revisa los logs de Laravel para ver el procesamiento completo\n";
echo "   tail -f storage/logs/laravel.log\n";


