# 👕 Sistema POS para Tienda de Ropa y Accesorios

Sistema web de punto de venta (POS) desarrollado para la gestión de una tienda de ropa y accesorios.

El proyecto permite administrar productos, inventario, clientes y ventas, generar facturas con código QR, consultar reportes gráficos y utilizar servicios externos como OpenWeather.

El sistema fue desarrollado como proyecto académico utilizando Laravel, PHP, MySQL, Bootstrap y JavaScript.

---

## 📌 Funcionalidades principales

El sistema cuenta con los siguientes módulos:

- Autenticación de usuarios.
- Control de acceso mediante roles.
- Gestión de productos.
- Gestión de inventario.
- Gestión de clientes.
- Punto de venta (POS).
- Registro de ventas.
- Generación de facturas.
- Código QR en facturas.
- Reportes de ventas.
- Gráficas estadísticas.
- Consulta del clima actual.
- API REST propia.
- Autenticación API mediante Laravel Sanctum.
- Diseño responsive para computadoras y dispositivos móviles.

---

# 🛠 Tecnologías utilizadas

## Backend

- PHP
- Laravel 13
- Laravel Sanctum
- Eloquent ORM
- REST API

## Base de datos

- MySQL

## Frontend

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- Bootstrap Icons
- Google Fonts
- Chart.js

## Herramientas

- Composer
- NPM
- Vite
- Git
- GitHub
- XAMPP / MySQL
- Visual Studio Code

---

# 🌐 Servicios y APIs externas

## OpenWeather

Se utiliza OpenWeather para obtener información meteorológica actual y mostrarla en el Dashboard.

La información presentada incluye:

- Temperatura actual.
- Sensación térmica.
- Humedad.
- Velocidad del viento.
- Descripción del clima.

La API Key se almacena en el archivo `.env` y nunca debe publicarse en GitHub.

Variables utilizadas:

```env
OPENWEATHER_API_KEY=TU_API_KEY
OPENWEATHER_LAT=15.0306
OPENWEATHER_LON=-91.1487
OPENWEATHER_LOCATION="Santa Cruz del Quiché"
```

---

## QR Code API

Las facturas generadas por el sistema incorporan un código QR.

El código contiene información básica de la venta, como:

- Número de factura.
- Total pagado.

Servicio utilizado:

```text
https://api.qrserver.com/
```

---

## Chart.js

Chart.js se utiliza en el módulo de reportes para representar gráficamente las ventas.

El sistema incluye gráficas de:

- Ventas diarias.
- Ventas semanales.
- Ventas mensuales.

---

# 👥 Roles del sistema

El sistema dispone de dos roles principales.

## Administrador

El Administrador puede:

- Acceder al Dashboard.
- Consultar productos.
- Crear productos.
- Editar productos.
- Eliminar productos.
- Gestionar clientes.
- Registrar ventas.
- Consultar facturas.
- Consultar reportes.
- Utilizar las operaciones administrativas de la API REST.

## Cajero

El Cajero puede:

- Acceder al Dashboard.
- Consultar productos.
- Gestionar clientes.
- Registrar ventas.
- Consultar facturas.

El Cajero no puede:

- Crear productos.
- Editar productos.
- Eliminar productos.
- Acceder a reportes administrativos.

Las restricciones se aplican tanto en la interfaz web como en la API REST.

---

# 🔐 Seguridad

El proyecto incorpora diferentes mecanismos de seguridad.

Entre ellos:

- Autenticación mediante sesiones.
- Contraseñas almacenadas mediante hash.
- Protección de rutas.
- Control de acceso basado en roles.
- Protección CSRF en formularios web.
- Validación de datos.
- Laravel Sanctum para la API REST.
- Tokens Bearer para endpoints protegidos.
- Variables sensibles almacenadas en `.env`.
- Códigos HTTP apropiados para respuestas de la API.

---

# 🛒 Punto de Venta

El módulo POS permite:

1. Buscar productos.
2. Agregar productos al carrito.
3. Modificar cantidades.
4. Eliminar productos del carrito.
5. Seleccionar un cliente.
6. Aplicar descuentos.
7. Calcular subtotal.
8. Calcular IVA del 12%.
9. Calcular total.
10. Seleccionar método de pago.
11. Registrar la venta.
12. Descontar automáticamente el stock.
13. Generar una factura.
14. Generar código QR.
15. Imprimir o guardar la factura como PDF.

Métodos de pago contemplados:

- Efectivo.
- Tarjeta.
- QR.

---

# 📦 Inventario

El sistema permite administrar productos con información como:

- Código.
- Nombre.
- Descripción.
- Precio.
- Stock.
- Categoría.
- Talla.
- Color.
- Género.
- Imagen.
- Estado.

También se muestra una alerta cuando un producto posee menos de 5 unidades disponibles.

---

# 👤 Clientes

El módulo de clientes permite:

- Registrar clientes.
- Consultar clientes.
- Editar clientes.
- Buscar por nombre.
- Buscar por NIT.
- Buscar por correo.
- Buscar por teléfono.

También se admite el consumidor final mediante NIT:

```text
CF
```

---

# 📊 Reportes

El Administrador dispone de un módulo de reportes que permite visualizar:

- Ventas del día.
- Ventas semanales.
- Ventas mensuales.
- Total de ingresos.
- IVA generado.
- Descuentos realizados.
- Productos más vendidos.
- Ventas dentro de un rango de fechas.
- Gráficas estadísticas.
- Historial de ventas.

Los reportes pueden imprimirse o guardarse como PDF utilizando la función de impresión del navegador.

---

# 🌤 Dashboard

El Dashboard presenta información resumida del sistema, incluyendo:

- Total de productos.
- Productos activos.
- Productos con stock bajo.
- Categorías registradas.
- Valor estimado del inventario.
- Productos agregados recientemente.
- Accesos rápidos.
- Información meteorológica mediante OpenWeather.

La información mostrada también cambia dependiendo del rol del usuario.

---

# 🔌 API REST

El proyecto incluye una API REST propia protegida mediante Laravel Sanctum.

Prefijo general:

```text
/api
```

Las respuestas se generan utilizando formato:

```text
JSON
```

---

# 🔑 Autenticación API

## Iniciar sesión

```http
POST /api/login
```

### Body JSON

```json
{
    "correo": "usuario@ejemplo.com",
    "password": "contraseña"
}
```

### Respuesta correcta

Código HTTP:

```text
200 OK
```

Ejemplo:

```json
{
    "ok": true,
    "message": "Autenticación correcta.",
    "token": "TOKEN_GENERADO_POR_SANCTUM",
    "usuario": {
        "id": 1,
        "nombre": "Administrador",
        "correo": "usuario@ejemplo.com",
        "rol": "administrador"
    }
}
```

El token debe enviarse posteriormente mediante:

```http
Authorization: Bearer TOKEN
```

---

# 📡 Endpoints disponibles

## 1. Iniciar sesión

```http
POST /api/login
```

Permite autenticar al usuario y obtener un token de acceso.

---

## 2. Listar productos

```http
GET /api/productos
```

Requiere autenticación.

Permite obtener todos los productos registrados.

También permite búsquedas:

```http
GET /api/productos?buscar=camisa
```

Respuesta:

```text
200 OK
```

---

## 3. Consultar producto

```http
GET /api/productos/{id}
```

Ejemplo:

```http
GET /api/productos/5
```

Respuesta:

```text
200 OK
```

---

## 4. Crear producto

```http
POST /api/productos
```

Requiere:

- Token Sanctum.
- Rol Administrador.

Ejemplo de Body:

```json
{
    "codigo": "CAM-001",
    "nombre": "Camisa Casual",
    "descripcion": "Camisa de prueba",
    "precio": 149.00,
    "stock": 10,
    "categoria_id": 1,
    "talla": "M",
    "color": "Negro",
    "genero": "unisex",
    "activo": true
}
```

Respuesta correcta:

```text
201 Created
```

Si un Cajero intenta crear un producto:

```text
403 Forbidden
```

---

## 5. Actualizar producto

```http
PUT /api/productos/{id}
```

Requiere rol:

```text
Administrador
```

Ejemplo:

```http
PUT /api/productos/5
```

Respuesta correcta:

```text
200 OK
```

---

## 6. Eliminar producto

```http
DELETE /api/productos/{id}
```

Requiere rol:

```text
Administrador
```

Respuesta correcta:

```text
200 OK
```

---

## 7. Listar clientes

```http
GET /api/clientes
```

Permite consultar clientes registrados.

También permite búsquedas:

```http
GET /api/clientes?buscar=Juan
```

Respuesta:

```text
200 OK
```

---

## 8. Consultar cliente

```http
GET /api/clientes/{id}
```

Ejemplo:

```http
GET /api/clientes/3
```

Respuesta:

```text
200 OK
```

---

## 9. Reporte de ventas

```http
GET /api/reportes/ventas
```

Disponible únicamente para:

```text
Administrador
```

Permite consultar también un rango de fechas:

```http
GET /api/reportes/ventas?fecha_inicio=2026-08-01&fecha_fin=2026-08-31
```

La respuesta incluye:

- Cantidad de ventas.
- Total de ingresos.
- IVA.
- Descuentos.
- Ventas realizadas.

---

## 10. Cerrar sesión API

```http
POST /api/logout
```

Elimina el token de acceso actual.

Respuesta:

```text
200 OK
```

---

# 📋 Resumen de endpoints

| Método | Endpoint | Descripción | Acceso |
|---|---|---|---|
| POST | `/api/login` | Iniciar sesión API | Público |
| GET | `/api/productos` | Listar productos | Autenticado |
| GET | `/api/productos/{id}` | Consultar producto | Autenticado |
| POST | `/api/productos` | Crear producto | Administrador |
| PUT | `/api/productos/{id}` | Actualizar producto | Administrador |
| DELETE | `/api/productos/{id}` | Eliminar producto | Administrador |
| GET | `/api/clientes` | Listar clientes | Autenticado |
| GET | `/api/clientes/{id}` | Consultar cliente | Autenticado |
| GET | `/api/reportes/ventas` | Reporte de ventas | Administrador |
| POST | `/api/logout` | Cerrar sesión API | Autenticado |

---

# 📟 Códigos HTTP utilizados

| Código | Significado |
|---|---|
| 200 | Operación realizada correctamente |
| 201 | Recurso creado correctamente |
| 401 | Credenciales incorrectas o usuario no autenticado |
| 403 | Usuario autenticado sin permisos suficientes |
| 404 | Recurso no encontrado |
| 422 | Error de validación |
| 500 | Error interno del servidor |

---

# 🗄 Base de datos

La aplicación utiliza una base de datos relacional MySQL.

Base de datos utilizada durante el desarrollo:

```text
pos_ropa
```

Entre las tablas principales se encuentran:

- users
- categorias
- productos
- clientes
- ventas
- detalle_ventas
- pagos
- personal_access_tokens

Las relaciones entre las tablas son gestionadas mediante Eloquent ORM.

---

# ⚙️ Instalación local

## 1. Clonar el repositorio

```bash
git clone https://github.com/Jeremias-stack/sis_ropa.git
```

Entrar al proyecto:

```bash
cd sis_ropa
```

Si se necesita trabajar con la rama de desarrollo:

```bash
git checkout jobat-dev
```

---

## 2. Instalar dependencias PHP

```bash
composer install
```

---

## 3. Instalar dependencias JavaScript

```bash
npm install
```

---

## 4. Crear archivo de entorno

En Windows:

```powershell
Copy-Item .env.example .env
```

También puede copiarse manualmente `.env.example` y renombrarse como:

```text
.env
```

---

## 5. Generar clave de Laravel

```bash
php artisan key:generate
```

---

## 6. Configurar MySQL

Crear una base de datos:

```text
pos_ropa
```

Configurar `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_ropa
DB_USERNAME=root
DB_PASSWORD=
```

---

## 7. Ejecutar migraciones y seeders

```bash
php artisan migrate --seed
```

---

## 8. Crear enlace para imágenes

```bash
php artisan storage:link
```

---

## 9. Configurar OpenWeather

Agregar al archivo `.env`:

```env
OPENWEATHER_API_KEY=TU_API_KEY
OPENWEATHER_LAT=15.0306
OPENWEATHER_LON=-91.1487
OPENWEATHER_LOCATION="Santa Cruz del Quiché"
```

Nunca se debe subir una API Key real al repositorio.

---

## 10. Limpiar configuración

```bash
php artisan optimize:clear
```

---

# ▶️ Ejecutar el proyecto

Se requieren dos terminales.

## Terminal 1 - Laravel

```bash
php artisan serve
```

Servidor local:

```text
http://127.0.0.1:8000
```

## Terminal 2 - Vite

```bash
npm run dev
```

Después ingresar desde el navegador a:

```text
http://127.0.0.1:8000
```

---

# 🧪 Prueba rápida de la API

Puede utilizarse Postman, Insomnia, PowerShell o cualquier cliente HTTP.

Ejemplo utilizando PowerShell.

## Login

```powershell
$body = @{
    correo = "USUARIO"
    password = "CONTRASEÑA"
} | ConvertTo-Json

$login = Invoke-RestMethod `
    -Uri "http://127.0.0.1:8000/api/login" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body
```

Guardar token:

```powershell
$token = $login.token

$headers = @{
    Authorization = "Bearer $token"
    Accept = "application/json"
}
```

Consultar productos:

```powershell
Invoke-RestMethod `
    -Uri "http://127.0.0.1:8000/api/productos" `
    -Method GET `
    -Headers $headers
```

---

# 🧾 Facturación

Después de registrar una venta, el sistema genera una factura que contiene:

- Número de factura.
- Fecha.
- Usuario que realizó la venta.
- Cliente.
- Productos vendidos.
- Cantidades.
- Precios.
- Subtotal.
- IVA.
- Descuento.
- Total.
- Método de pago.
- Código QR.

La factura dispone de una opción para imprimir o guardar como PDF.

---

# 📱 Diseño responsive

La interfaz fue diseñada para adaptarse a diferentes tamaños de pantalla.

Se realizaron ajustes específicos para:

- Computadora de escritorio.
- Laptop.
- Tablet.
- Teléfono móvil.

El menú lateral se transforma en un menú desplegable en dispositivos pequeños.

---

# 📁 Estructura general

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── DashboardController.php
│   │   ├── ProductoController.php
│   │   ├── ReporteController.php
│   │   └── VentaController.php
│   └── Middleware/
│       └── RoleMiddleware.php
│
├── Models/
│   ├── Categoria.php
│   ├── Cliente.php
│   ├── DetalleVenta.php
│   ├── Pago.php
│   ├── Producto.php
│   ├── User.php
│   └── Venta.php
│
routes/
├── web.php
└── api.php
│
resources/
├── css/
├── js/
└── views/
│
database/
├── migrations/
└── seeders/
```

---

# 🔄 Control de versiones

El proyecto utiliza Git y GitHub para el control de versiones.

Repositorio:

```text
https://github.com/Jeremias-stack/sis_ropa
```

Rama de desarrollo utilizada:

```text
jobat-dev
```

---

# 🔒 Consideraciones importantes

No deben publicarse en GitHub:

```text
.env
```

Tampoco deben publicarse:

- API Keys.
- Tokens de Sanctum.
- Contraseñas reales.
- Credenciales de producción.

Las credenciales utilizadas para demostraciones deben considerarse únicamente datos de prueba y deben cambiarse antes de utilizar el sistema en un entorno real.

---

# 🎓 Proyecto académico

Sistema POS Web para una Tienda de Ropa y Accesorios.

Desarrollado con fines académicos utilizando tecnologías web, una base de datos relacional, servicios externos y una API REST propia.

---

## Estado del proyecto

Actualmente el sistema cuenta con:

- [x] Autenticación
- [x] Roles Administrador y Cajero
- [x] Control de permisos
- [x] Gestión de productos
- [x] Control de inventario
- [x] Imágenes de productos
- [x] Alertas de stock
- [x] Gestión de clientes
- [x] Punto de venta
- [x] IVA y descuentos
- [x] Registro de ventas
- [x] Facturación
- [x] Código QR
- [x] Reportes
- [x] Chart.js
- [x] OpenWeather
- [x] API REST
- [x] Laravel Sanctum
- [x] Diseño responsive
- [ ] Despliegue en hosting público

---

# 📄 Licencia

Proyecto desarrollado con fines educativos y académicos.