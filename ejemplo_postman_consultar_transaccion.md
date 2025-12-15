# Ejemplo Postman - Consultar Transacción PagoFácil

## 📋 Datos de tu última transacción:
- **pagofacilTransactionId**: `6764538`
- **companyTransactionId**: `FICHA-f45f0935-8e6a-4db8-a185-1627dd684e68-1764293835`

---

## 🔑 PASO 1: Obtener Bearer Token

### Request 1: Login para obtener token

**Método:** `POST`

**URL:**
```
https://masterqr.pagofacil.com.bo/api/services/v2/login
```

**Headers:**
```
tcTokenService: [TU_PAGOFACIL_TC_TOKEN_SERVICE]
tcTokenSecret: [TU_PAGOFACIL_TC_TOKEN_SECRET]
```

**Body:** (vacío, no enviar body)

**Ejemplo en Postman:**
1. Crea una nueva request
2. Método: `POST`
3. URL: `https://masterqr.pagofacil.com.bo/api/services/v2/login`
4. En la pestaña **Headers**, agrega:
   - Key: `tcTokenService` | Value: `[tu valor de .env PAGOFACIL_TC_TOKEN_SERVICE]`
   - Key: `tcTokenSecret` | Value: `[tu valor de .env PAGOFACIL_TC_TOKEN_SECRET]`
5. Envía la request
6. **Copia el token** de la respuesta. Estará en: `values.accessToken` o `accessToken`

**Respuesta esperada:**
```json
{
  "values": {
    "accessToken": "4qw5fretyrtyrtgrey..."
  }
}
```

---

## 🔍 PASO 2: Consultar Transacción

### Request 2: Query Transaction

**Método:** `POST`

**URL:**
```
https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction
```

**Headers:**
```
Authorization: Bearer [EL_TOKEN_QUE_OBTUVISTE_EN_PASO_1]
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "pagofacilTransactionId": "6764538" 6765692
}
```

**O alternativamente (solo necesitas uno):**
```json
{
  "companyTransactionId": "FICHA-f45f0935-8e6a-4db8-a185-1627dd684e68-1764293835"
}
```

**Ejemplo en Postman:**
1. Crea una nueva request
2. Método: `POST`
3. URL: `https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction`
4. En la pestaña **Headers**, agrega:
   - Key: `Authorization` | Value: `Bearer [PEGA_AQUI_EL_TOKEN_DEL_PASO_1]`
   - Key: `Content-Type` | Value: `application/json`
5. En la pestaña **Body**, selecciona `raw` y `JSON`
6. Pega el JSON de arriba (usa solo uno de los dos IDs)
7. Envía la request

---

## 📝 Resumen rápido:

### Para obtener el Bearer:
- **POST** `https://masterqr.pagofacil.com.bo/api/services/v2/login`
- **Headers:** `tcTokenService` y `tcTokenSecret` (valores de tu `.env`)
- **Body:** vacío

### Para consultar transacción:
- **POST** `https://masterqr.pagofacil.com.bo/api/services/v2/query-transaction`
- **Headers:** `Authorization: Bearer [TOKEN]`
- **Body:** `{"pagofacilTransactionId": "6764538"}`

---

## 🔐 Dónde obtener las credenciales:

Las credenciales están en tu archivo `.env`:
- `PAGOFACIL_TC_TOKEN_SERVICE=...`
- `PAGOFACIL_TC_TOKEN_SECRET=...`

Si no las tienes, revisa tu archivo `.env` o contacta a PagoFácil para obtenerlas.


