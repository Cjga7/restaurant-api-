# 🍽️ RestaurantOS API

> Sistema de gestión integral para restaurantes multi-sucursal con actualizaciones en tiempo real.

API REST construida con **Laravel 11** que implementa un sistema completo de gestión para restaurantes con múltiples sucursales: desde la toma de pedidos hasta el control de stock, caja y reportes, con un Kitchen Display System en tiempo real vía WebSockets.

🔗 **Frontend del proyecto:** [restaurant-app](https://github.com/Cjga7/restaurant-app)

---

## ✨ Características principales

- 🏢 **Multi-sucursal** — Cada empleado ve solo los datos de su sucursal automáticamente (Global Scope)
- 🔐 **Autenticación robusta** — Laravel Sanctum + Spatie Permission con 5 roles y permisos granulares
- 🍔 **Recetas inteligentes** — Cada producto del menú define ingredientes que se descuentan automáticamente del stock al cobrar
- 💸 **Caja por cajero** — Cada cajero abre y cierra su propia sesión con cuadre automático
- 📊 **Reportes en tiempo real** — KPIs, gráficos de ventas, productos top, performance por cajero
- ⚡ **Kitchen Display System** — Los pedidos aparecen en la cocina al instante vía WebSockets
- 🧾 **Tickets imprimibles** — Tickets de cocina y recibos de venta en formato 80mm para impresoras térmicas

---

## 🏗️ Arquitectura

El proyecto sigue el patrón **Controller → Service → Repository** en todos los módulos:
HTTP Request
↓
Controller (validación + transformación HTTP)
↓
Service (lógica de negocio + transacciones)
↓
Repository (acceso a datos vía Eloquent)
↓
Database (MySQL)

### Decisiones de diseño clave

- **Empleados ≠ Usuarios** — Los empleados son del módulo de RRHH, mientras que los usuarios son las credenciales del sistema. Se vinculan opcionalmente.
- **Caja por cajero** — Cada empleado abre y cierra su propia sesión, garantizando responsabilidad individual sobre el cuadre.
- **Recetas con descuento al cobrar** — El stock se descuenta cuando el pedido pasa a `pagado`, no al enviarlo a cocina.
- **Transferencias = 2 movimientos vinculados** — Una transferencia entre sucursales genera un movimiento de salida en origen + entrada en destino, en una transacción atómica.
- **Reservas con flujo profesional** — Botón "Cliente llegó" que crea automáticamente un pedido vinculado a la mesa.

---

## 📦 Módulos implementados

| Módulo | Endpoints | Características |
|--------|-----------|----------------|
| **Auth & Roles** | `/auth/*`, `/users/*`, `/roles` | Login con Sanctum, CRUD de usuarios, asignación de roles |
| **Sucursales** | `/sucursales/*` | CRUD multi-sucursal con scope global |
| **Menú** | `/menu/*`, `/productos/*`, `/recetas/*` | Categorías, productos con imágenes, recetas |
| **Mesas** | `/mesas/*` | Estados (disponible/ocupada/reservada), QR único |
| **Empleados** | `/empleados/*` | RRHH con vinculación opcional a usuarios |
| **Reservas** | `/reservas/*` | Flujo: pendiente → confirmada → cliente llegó (crea pedido) |
| **Pedidos** | `/pedidos/*`, `/pedidos/{id}/items/*` | State machine, número correlativo diario por sucursal |
| **Inventario** | `/inventario/*` | Stock por sucursal, movimientos, transferencias |
| **Caja/Pagos** | `/caja/*`, `/pagos/*` | Sesiones por cajero, métodos múltiples, cuadre automático |
| **Reportes** | `/reportes/*` | Resumen, ventas por día, top productos, métodos de pago, cajeros, stock crítico |
| **Real-time** | WebSockets | Kitchen Display vía Laravel Reverb |

---

## 🛠️ Stack técnico

- **Framework:** Laravel 11
- **PHP:** 8.3+
- **Base de datos:** MySQL 8
- **Autenticación:** Laravel Sanctum
- **Autorización:** Spatie Laravel Permission
- **WebSockets:** Laravel Reverb
- **Storage:** Local con symlink para imágenes

---

## 🚀 Instalación

### Requisitos previos

- PHP 8.3+
- Composer
- MySQL 8+
- Node.js 20+ (solo para Reverb)

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/Cjga7/restaurant-api-.git
cd restaurant-api-

# 2. Instalar dependencias de Composer
composer install

# 3. Copiar el archivo de entorno
cp .env.example .env

# 4. Generar la app key
php artisan key:generate

# 5. Configurar las variables del .env
# - DB_DATABASE, DB_USERNAME, DB_PASSWORD
# - REVERB_APP_ID, REVERB_APP_KEY, REVERB_APP_SECRET (cualquier string aleatorio)

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Crear el symlink de storage para imágenes
php artisan storage:link

# 8. Levantar el servidor
php artisan serve
```

### Para WebSockets (en otra terminal)

```bash
php artisan reverb:start
```

---

## 🔑 Roles y permisos

El sistema viene con 5 roles preconfigurados:

| Rol | Acceso |
|-----|--------|
| **super_admin** | Todo el sistema, todas las sucursales |
| **gerente_sucursal** | Administra su sucursal completa |
| **cajero** | Procesa pagos y maneja la caja |
| **mozo** | Toma pedidos y gestiona mesas |
| **cocinero** | Ve pedidos en el Kitchen Display |

Los permisos están definidos en `database/seeders/RolePermissionSeeder.php` y son granulares por módulo (ej: `pedidos.ver`, `pedidos.crear`, `pedidos.gestionar`).

---

## 📐 Estructura del proyecto
app/
├── Events/                  # Eventos broadcast (WebSockets)
├── Http/
│   └── Controllers/Api/     # Controllers REST
├── Models/                  # Modelos Eloquent
├── Repositories/            # Capa de acceso a datos
├── Scopes/                  # Global scopes (ej: SucursalScope)
└── Services/                # Lógica de negocio
database/
├── migrations/              # Migraciones de tablas
└── seeders/                 # Seeders (roles, permisos)
routes/
├── api.php                  # Rutas de la API
└── channels.php             # Canales de broadcasting

---

## 🔄 Flujos de negocio destacados

### Flujo completo de una orden

Mozo crea pedido → Mesa: ocupada
Mozo agrega items → Pedido: abierto
Mozo "envía a cocina" → Pedido: enviado
→ 🔔 WebSocket notifica al KDS
→ Cocinero ve el pedido al instante
Cocinero "empieza a preparar" → Pedido: en_preparacion
Cocinero "marca listo" → Pedido: listo
Mozo entrega → Pedido: entregado
Cajero cobra → Pedido: pagado
→ Mesa: disponible
→ Stock descontado según recetas
→ Recibo impreso


### Cobro de un pedido

```php
// Al cobrar, en una sola transacción:
1. Se crea el registro de pago (vinculado a la sesión del cajero)
2. Se cambia el estado del pedido a "pagado"
3. Se descuenta el stock según las recetas del producto
4. Se libera la mesa
5. Se emite evento broadcast para sincronizar UIs
```

---

## 📡 WebSockets en tiempo real

El sistema usa **Laravel Reverb** para enviar actualizaciones en tiempo real al frontend. Cada vez que un pedido se crea, actualiza o cambia de estado, se emite un evento al canal de la sucursal correspondiente:

```php
// app/Events/PedidoActualizado.php
broadcast(new PedidoActualizado($pedido, 'estado_cambiado'));
// → Canal: pedidos.sucursal.{sucursal_id}
// → Evento: pedido.actualizado
```

Esto permite que el Kitchen Display, la pantalla de mozos y la pantalla de caja se sincronicen sin recargar.

---

## 🧪 Testing manual con Postman

Una vez instalado, podés probar los endpoints:

```bash
# 1. Login
POST /api/auth/login
{
  "email": "admin@restaurant.test",
  "password": "password"
}

# 2. Usar el token devuelto en el header
Authorization: Bearer {tu_token}

# 3. Probar endpoints
GET /api/sucursales
GET /api/menu/categorias
GET /api/mesas
# ... etc
```

---

## 👨‍💻 Autor

**Cristian Josue Garcia Alanis**

- GitHub: [@Cjga7](https://github.com/Cjga7)

---

## 📄 Licencia

Proyecto de portafolio de uso libre con fines educativos.