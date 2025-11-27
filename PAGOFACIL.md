# PagoFácil - Especificaciones Integración por API

[cite\_start]**Versión:** 1.1.0 [cite: 5]
[cite\_start]**Fecha:** 02/07/2025 [cite: 7]

-----

## 1\. Control de Versiones

| Descripción | Versión | Autor | Fecha |
| :--- | :--- | :--- | :--- |
| Creación del documento | 1.0.0 | Juan Pablo | [cite\_start]2025-06-17 [cite: 18] |
| Actualización de la documentación | 1.1.0 | Juan Pablo | [cite\_start]2025-07-02 [cite: 18] |

## 2\. Consideraciones Iniciales

| Título | Detalle |
| :--- | :--- |
| **Título** | [cite\_start]PagoFacilServiciosAPI [cite: 20] |
| **Versión** | [cite\_start]1.0.0 [cite: 20] |
| **Protocolo** | [cite\_start]HTTPS [cite: 20] |
| **URL BASE** | [cite\_start]`https://masterqr.pagofacil.com.bo/api/services/v2` [cite: 20] |

-----

## 3\. Flujo del Proceso

[cite\_start]El proceso general de pago sigue estos pasos [cite: 51-60]:

1.  **Autenticación:** El comercio obtiene un `accessToken`.
2.  **Generar QR:** El comercio envía los detalles de la transacción y recibe la imagen del QR.
3.  **Mostrar QR:** Se presenta el QR al cliente final.
4.  **Pago:** El cliente escanea y paga con su app bancaria.
5.  **Confirmación:** El comercio recibe una notificación automática (Callback) o consulta manualmente el estado.

-----

## 4\. Descripción de los Servicios

### 4.1. Autenticación

[cite\_start]Este servicio permite obtener el token necesario para utilizar los demás endpoints[cite: 68].

  * [cite\_start]**Método:** `POST` [cite: 64]
  * [cite\_start]**Endpoint:** `/login` [cite: 65]
  * [cite\_start]**Header Requerido:** Credenciales (`tcTokenService` y `tcTokenSecret`)[cite: 70].

**Ejemplo de Uso (Header):**

```json
{
  "tcTokenService": "ewgbjukaewqrq...",
  "tcTokenSecret": "qweeretyrtyrtgrey..."
}
```

[cite\_start][cite: 76-79]

**Respuesta Exitosa:**

```json
{
  "error": 0,
  "status": 1,
  "message": "Autenticación exitosa.",
  "values": {
    "accessToken": "eyJ0eXAIYIRI_v2Ak...",
    "tokenType": "bearer",
    "expiresInMinutes": 789.71
  }
}
```

[cite\_start][cite: 83-91]

-----

### 4.2. Listar Métodos Habilitados

[cite\_start]Obtiene la lista de métodos de pago QR configurados para el comercio, incluyendo el `paymentMethodId` necesario para generar QRs [cite: 100-102].

  * [cite\_start]**Método:** `POST` [cite: 96]
  * [cite\_start]**Endpoint:** `/list-enabled-services` [cite: 97]
  * [cite\_start]**Header Requerido:** `Authorization: Bearer <tu_access_token>`[cite: 105].

**Respuesta Exitosa:**

```json
{
  "error": 0,
  "status": 2006,
  "message": "Lista de servicios QR habilitados...",
  "values": [
    {
      "paymentMethodId": 4, 
      "paymentMethodName": "QR ...",
      "currencyName": "BOB",
      "maxAmountPerDay": 10000.00,
      "maxAmountPerTransaction": 5000.00
    }
  ]
}
```

[cite\_start][cite: 108-118]

-----

### 4.3. Generar QR

[cite\_start]Este es el endpoint central para iniciar una transacción y obtener el código QR[cite: 127].

  * [cite\_start]**Método:** `POST` [cite: 123]
  * [cite\_start]**Endpoint:** `/generate-qr` [cite: 124]
  * [cite\_start]**Header Requerido:** `Authorization: Bearer <tu_access_token>`[cite: 130].

#### Ejemplo de Implementación (Código)

*Basado en el archivo "Ejemplo generacion qr.jpeg" proporcionado por el usuario.*

```javascript
async genQr() {
  const headersRequest = {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zzZXJ2aW...' // Token de ejemplo
  };

  const laPrepararPago = await firstValueFrom(
    this.httpService.post('https://masterqr.pagofacil.com.bo/api/services/v2/generate-qr',
      {
        paymentMethod: 4,
        clientName: "Jhon Doe",
        documentType: 1,
        documentId: "123456",
        phoneNumber: "75540850",
        email: "mario.herbas@pagofacil.com.bo",
        paymentNumber: "123Prueba1",
        amount: 0.1,
        currency: 2,
        clientCode: "11001",
        callbackUrl: "https://tu-dominio.com/callback",
        orderDetail: [
          {
            "serial": 1,
            "product": "Detalle_Item",
            "quantity": 1,
            "price": 0.1,
            "discount": 0,
            "total": 0.1
          }
        ]
      },
      {
        headers: headersRequest,
      }
    ),
  );

  const laRespuestaPrepararPago = laPrepararPago.data;
  console.log("laRespuestaPrepararPago : ", laRespuestaPrepararPago);
  return laRespuestaPrepararPago;
}
```

**Respuesta Estándar Exitosa:**

```json
{
  "error": 0,
  "status": 2007,
  "message": "QR Generado Correctamente.",
  "values": {
    "transactionId": "Id_Transaccion_PagoFacil",
    "paymentMethodTransactionId": "Id_Transaccion_Empresa",
    "status": 1,
    "expirationDate": "Y-m-d H:i:s",
    "qrBase64": "BF9n8cbrS48hRv9/z2dFX73kegDYcw...",
    "checkoutUrl": "Url_Link", 
    "deepLink": "App_Link", 
    "qrContentUrl": "Url_Link", 
    "universalUrl": "Url_Link"
  }
}
```

[cite\_start][cite: 158-175]

-----

### 4.4. Consultar Transacción

[cite\_start]Verifica el estado actual de una transacción[cite: 180].

  * [cite\_start]**Método:** `POST` [cite: 178]
  * [cite\_start]**Endpoint:** `/query-transaction` [cite: 179]

**Cuerpo (Body):**
Se requiere solo uno de los siguientes IDs:

```json
{
  "pagofacilTransactionId": "Id_Transaccion_PagoFacil", 
  "companyTransactionId": "Id_Transaccion_Empresa"
}
```

[cite\_start][cite: 189]

**Respuesta Exitosa:**
[cite\_start]Devuelve el estado (`paymentStatus`), fecha y hora del pago [cite: 195-203].

-----

### 4.5. Consultar Bibliografía

[cite\_start]Permite consultar reglas y ejemplos de los parámetros de la API[cite: 213].

  * [cite\_start]**Método:** `GET` [cite: 209]
  * [cite\_start]**Endpoint:** `/bibliography/{nombreParametro}` [cite: 219]

-----

## 5\. Configuraciones Adicionales

### Idioma de Respuestas

[cite\_start]Se puede configurar el idioma de los mensajes de respuesta enviando un parámetro en el encabezado[cite: 251].

  * [cite\_start]**Header:** `Response-Language: en` o `Response-Language: es`[cite: 256].
  * [cite\_start]Por defecto: Inglés[cite: 252].

### Notificaciones (Callback)

[cite\_start]Si se envía el parámetro `callbackUrl` al generar el QR, PagoFácil notificará el pago a esa URL mediante `POST`[cite: 289, 292].

**Estructura de la Notificación recibida:**

```json
{
  "PedidoID": "Numero de venta/factura del comercio",
  "Fecha": "Fecha de realización del pago",
  "Hora": "Hora del pago",
  "MetodoPago": "Medio por el que se realizo el pago",
  "Estado": "Estado del pago"
}
```

[cite\_start][cite: 296-300]

**Respuesta esperada por PagoFácil:**
Tu servidor debe responder con HTTP 200 OK y el siguiente JSON:

```json
{
  "error": 0,
  "status": 1,
  "message": "Mensaje personalizado de éxito",
  "values": true
}
```

[cite\_start][cite: 304-309]

-----

## 6\. Soporte Técnico

Para coordinación de credenciales o consultas técnicas:

  * [cite\_start]**Correo:** `atencion.cliente@pagofacil.com.bo` [cite: 319]
  * [cite\_start]**Celular Soporte:** `(+591) 75353593` [cite: 321]