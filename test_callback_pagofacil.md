# 🧪 Script de Prueba - Callback PagoFácil

## 📋 Instrucciones para Probar el Callback

### 1. Obtener un `company_transaction_id` de prueba

Primero necesitas tener un pago creado en tu base de datos. Puedes:

**Opción A: Crear un pago de prueba manualmente**
```sql
-- Buscar una ficha existente
SELECT id, cliente_id, servicio_id FROM fichas LIMIT 1;

-- Insertar un pago de prueba (reemplaza los valores)
INSERT INTO pagos (
    id, 
    ficha_id, 
    monto, 
    tipo, 
    metodo_pago, 
    estado, 
    company_transaction_id,
    qr_status,
    created_at,
    updated_at
) VALUES (
    gen_random_uuid()::text,
    'ID_DE_TU_FICHA_AQUI',
    100.00,
    'CONTADO',
    'QR',
    'PENDIENTE',
    'FICHA-TEST-12345',
    'PENDING',
    NOW(),
    NOW()
);
```

**Opción B: Usar un pago existente**
```sql
-- Ver pagos QR pendientes
SELECT id, company_transaction_id, estado, qr_status 
FROM pagos 
WHERE metodo_pago = 'QR' 
AND qr_status = 'PENDING'
LIMIT 1;
```

### 2. Probar el Callback con cURL

**Ejemplo 1: Pago Completado (según documentación oficial)**

```bash
curl -X POST http://tu-dominio.com/cliente/pagos/callback \
  -H "Content-Type: application/json" \
  -d '{
    "PedidoID": "FICHA-TEST-12345",
    "Estado": "Completado",
    "Fecha": "2025-01-27",
    "Hora": "14:30:00",
    "MetodoPago": "QR"
  }'
```

**Ejemplo 2: Pago Completado (formato alternativo)**

```bash
curl -X POST http://tu-dominio.com/cliente/pagos/callback \
  -H "Content-Type: application/json" \
  -d '{
    "PedidoID": "FICHA-TEST-12345",
    "Estado": "PAID",
    "Fecha": "2025-01-27",
    "Hora": "15:45:30",
    "MetodoPago": "QR Simple"
  }'
```

**Ejemplo 3: Pago Pendiente**

```bash
curl -X POST http://tu-dominio.com/cliente/pagos/callback \
  -H "Content-Type: application/json" \
  -d '{
    "PedidoID": "FICHA-TEST-12345",
    "Estado": "Pendiente",
    "Fecha": "2025-01-27",
    "Hora": "16:00:00",
    "MetodoPago": "QR"
  }'
```

### 3. Probar con Postman

1. **Método:** POST
2. **URL:** `http://tu-dominio.com/cliente/pagos/callback`
3. **Headers:**
   - `Content-Type: application/json`
4. **Body (raw JSON):**
```json
{
  "PedidoID": "FICHA-TEST-12345",
  "Estado": "Completado",
  "Fecha": "2025-01-27",
  "Hora": "14:30:00",
  "MetodoPago": "QR"
}
```

### 4. Probar con PHP (desde tinker o script)

```php
// Desde Laravel Tinker: php artisan tinker
use Illuminate\Support\Facades\Http;

$response = Http::post('http://tu-dominio.com/cliente/pagos/callback', [
    'PedidoID' => 'FICHA-TEST-12345',
    'Estado' => 'Completado',
    'Fecha' => '2025-01-27',
    'Hora' => '14:30:00',
    'MetodoPago' => 'QR'
]);

dd($response->json());
```

### 5. Verificar los Logs

Después de enviar el callback, revisa los logs de Laravel:

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# O buscar específicamente
grep "PagoFácil" storage/logs/laravel.log | tail -20
```

**Logs esperados:**
- `📞 [PagoFácil] Callback recibido`
- `🔍 [PagoFácil] Datos extraídos del callback (según documentación)`
- `📋 [PagoFácil] Pago encontrado`
- `✅ [PagoFácil] ¡PAGO CONFIRMADO EN CALLBACK!`
- `💾 [PagoFácil] Pago actualizado en BD`

### 6. Verificar en la Base de Datos

```sql
-- Ver el pago actualizado
SELECT 
    id,
    company_transaction_id,
    estado,
    qr_status,
    fecha_pago,
    metodo_pago,
    updated_at
FROM pagos
WHERE company_transaction_id = 'FICHA-TEST-12345';

-- Debería mostrar:
-- estado: 'PAGADO'
-- qr_status: 'PAID'
-- fecha_pago: La fecha/hora del callback (no now())
```

### 7. Respuesta Esperada

**Si todo está correcto:**
```json
{
  "error": 0,
  "status": 1,
  "message": "Notificación recibida correctamente",
  "values": true
}
```

**Si falta PedidoID:**
```json
{
  "error": 1,
  "message": "PedidoID no encontrado"
}
```

**Si el pago no existe:**
```json
{
  "error": 1,
  "message": "Pago no encontrado"
}
```

## 🔍 Qué Verificar

1. ✅ **Todos los campos se capturan:** PedidoID, Estado, Fecha, Hora, MetodoPago
2. ✅ **Fecha y Hora se usan del callback:** No se usa `now()`, se usa la fecha/hora del callback
3. ✅ **El pago se actualiza correctamente:** estado = 'PAGADO', qr_status = 'PAID'
4. ✅ **La respuesta es correcta:** Formato JSON según documentación
5. ✅ **Los logs muestran todos los datos:** Revisar logs para debugging

## 🚨 Errores Comunes

1. **"Pago no encontrado"**
   - Verifica que el `PedidoID` coincida con un `company_transaction_id` en la BD
   - Verifica que el pago exista

2. **"PedidoID no encontrado"**
   - Verifica que estés enviando el campo `PedidoID` en el JSON
   - Verifica que el Content-Type sea `application/json`

3. **Fecha/Hora no se parsean**
   - Verifica el formato: Fecha debe ser "Y-m-d" (ej: "2025-01-27")
   - Verifica el formato: Hora debe ser "H:i:s" (ej: "14:30:00")
   - Si no vienen, se usará `now()` como fallback

## 📝 Notas

- El callback debe ser accesible desde internet (no funciona en localhost)
- Para desarrollo local, usa herramientas como ngrok o similar
- El callback se ejecuta sin autenticación (ruta pública)
- Siempre responde con HTTP 200 y el formato JSON esperado

