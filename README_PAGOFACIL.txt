# Integración PagoFácil - Guía de implementación

## Configuración

### 1. Variables de entorno

Agrega las siguientes variables en tu archivo `.env`:

```env
PAGOFACIL_BASE_URL=https://masterqr.pagofacil.com.bo/api/services/v2
PAGOFACIL_TOKEN_SERVICE=tu_token_service_aqui
PAGOFACIL_TOKEN_SECRET=tu_token_secret_aqui
```

**Nota:** Solicita las credenciales (`tcTokenService` y `tcTokenSecret`) al equipo de PagoFácil:
- Email: atencion.cliente@pagofacil.com.bo
- Celular: (+591) 75353593

### 2. Instalación

```bash
# Ejecutar migraciones (ya ejecutadas)
php artisan migrate

# Compilar assets de frontend
npm run dev
```

## Uso

### Acceder a la vista de pago

1. Inicia el servidor de desarrollo:
```bash
php artisan serve
```

2. Accede a: `http://localhost:8000/pago`

### Flujo de la aplicación

1. **Formulario de pago**: El usuario ingresa sus datos (nombre, email, teléfono, monto)
2. **Generar QR**: Al enviar el formulario, se genera un código QR
3. **Escanear QR**: El usuario escanea el QR con su app bancaria
4. **Pago automático**: La aplicación detecta automáticamente cuando se completa el pago
5. **Confirmación**: Se muestra un mensaje de éxito

## Arquitectura

### Backend (Laravel)

- **Modelo**: `Transaction` - Gestiona las transacciones de pago
- **Servicio**: `PagoFacilService` - Maneja la comunicación con la API de PagoFácil
  - `authenticate()` - Obtiene token de acceso
  - `generateQr()` - Genera código QR de pago
  - `queryTransaction()` - Consulta estado de transacción
- **Controlador**: `PaymentController`
  - `create()` - Crea transacción y genera QR
  - `status()` - Consulta estado de pago
  - `callback()` - Recibe notificaciones de PagoFácil

### Frontend (Vue 3 + Inertia)

- **Vista**: `Payment.vue` - Interfaz de usuario para el proceso de pago
  - Formulario de datos del cliente
  - Visualización de código QR
  - Verificación automática cada 3 segundos
  - Confirmación de pago exitoso

### Base de datos

Tabla `transactions`:
- `transaction_id` - ID de PagoFácil
- `payment_number` - Número de orden único
- `client_name`, `email`, `phone_number` - Datos del cliente
- `amount` - Monto del pago
- `status` - Estado (1=pendiente, 2=pagado, 3=expirado, 4=error)
- `qr_base64` - Imagen QR en base64
- `expiration_date` - Fecha de expiración
- `paid_at` - Fecha de pago

## Endpoints

### API Routes

```php
POST   /pago/crear           # Crear transacción y generar QR
GET    /pago/estado/{id}     # Consultar estado de transacción
POST   /pagofacil/callback   # Callback de PagoFácil (webhook)
```

### Web Routes

```php
GET    /pago                 # Vista de pago
```

## Callback (Webhook)

PagoFácil enviará notificaciones POST a tu URL de callback cuando se complete un pago:

**URL configurada**: `https://tu-dominio.com/pagofacil/callback`

**Estructura esperada**:
```json
{
  "PedidoID": "ORD-1234567890-1234",
  "Fecha": "2025-11-27",
  "Hora": "15:30:00",
  "MetodoPago": "QR",
  "Estado": "Pagado"
}
```

**Respuesta requerida**:
```json
{
  "error": 0,
  "status": 1,
  "message": "Pago recibido correctamente",
  "values": true
}
```

## Testing

### Datos de prueba

- **Monto mínimo**: 0.1 BOB
- **Email**: Cualquier email válido
- **Teléfono**: Formato boliviano (ej: 75540850)

### Verificación manual

1. Crea una transacción desde `/pago`
2. Verifica en la base de datos:
```bash
php artisan tinker
>>> \App\Models\Transaction::latest()->first()
```

## Principios aplicados

✅ **KISS (Keep It Simple, Stupid)**
- Código claro y directo
- Sin abstracciones innecesarias
- Funcionalidad core sin complicaciones

✅ **YAGNI (You Aren't Gonna Need It)**
- Solo las características necesarias
- Sin preparación para casos futuros
- Implementación mínima viable

## Estructura de archivos

```
app/
├── Http/Controllers/
│   └── PaymentController.php
├── Models/
│   └── Transaction.php
└── Services/
    └── PagoFacilService.php

database/migrations/
└── 2025_11_27_000001_create_transactions_table.php

resources/js/pages/
└── Payment.vue

routes/
└── web.php

config/
└── services.php
```
