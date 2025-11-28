# Configuración de PagoFácil

## Variables de Entorno

Agrega estas variables a tu archivo `.env`:

```env
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo
PAGOFACIL_API_URL=https://masterqr.pagofacil.com.bo/api/services/v2
PAGOFACIL_TC_TOKEN_SERVICE=51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d504a62547a9de71bfc76be2c2ae01039ebcb0f74a96f0f1f56542c8b51ef7a2a6da9ea16f23e52ecc4485b69640297a5ec6a701498d2f0e1b4e7f4b7803bf5c2eba
PAGOFACIL_TC_TOKEN_SECRET=0C351C6679844041AA31AF9C
```

## Credenciales

- **PAGOFACIL_TC_TOKEN_SERVICE**: Tu `tcTokenService` (identificador de servicio)
- **PAGOFACIL_TC_TOKEN_SECRET**: Tu `tcTokenSecret` (clave secreta)

## Cómo Probar

1. **Configura las variables en `.env`** con tus credenciales reales.

2. **Limpia la caché de configuración**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Prueba la autenticación**:
   - El servicio intentará autenticarse automáticamente cuando generes un QR
   - Revisa los logs en `storage/logs/laravel.log` para ver el proceso

4. **Genera un QR de prueba**:
   - Ve a una ficha de cliente
   - Haz clic en "Procesar Pago"
   - Selecciona "QR" como método de pago
   - El sistema intentará autenticarse y generar el QR

## Endpoint de Autenticación

El servicio se autentica en:
- `https://masterqr.pagofacil.com.bo/api/services/v2/login`

Las credenciales (`tcTokenService` y `tcTokenSecret`) se envían en el Header de la petición POST.

## Logs

Todos los pasos se registran en `storage/logs/laravel.log` con prefijos:
- 🔐 Autenticación
- 🔑 Token en caché
- 🌐 Generación de QR
- 📥 Respuestas
- ❌ Errores

## Notas

- El Bearer token se guarda en caché por 1 hora para evitar autenticaciones innecesarias
- Si cambias las credenciales, limpia la caché: `php artisan cache:clear`

