# API REST de SIS_ROPA — guía breve

## Dos conjuntos de API en una sola plataforma

**API Laravel:** `/api/*` de aplicación comercial, protegida con token Laravel Sanctum. **API distribuida:** rutas `/api/auth/*`, `/api/inventario/*`, `/api/ventas*`, `/api/reportes/pos/*` en el gateway Nginx del puerto 8090; usa JWT del microservicio Auth. **No son el mismo sistema de autenticación.**

### A. Laravel (Sanctum)

| Método | URL | Función | Acceso |
|---|---|---|---|
| POST | `/api/login` | Generar token Sanctum | Credenciales válidas |
| POST | `/api/logout` | Revocar token | Admin/cajero |
| GET | `/api/productos` | Listado y búsqueda | Admin/cajero |
| GET | `/api/productos/{producto}` | Detalle y variantes | Admin/cajero |
| POST | `/api/productos` | Crear producto | Administrador |
| PUT | `/api/productos/{producto}` | Actualizar producto | Administrador |
| DELETE | `/api/productos/{producto}` | Desactivar producto | Administrador |
| GET | `/api/clientes` | Lista de clientes | Admin/cajero |
| GET | `/api/clientes/{cliente}` | Detalle de cliente | Admin/cajero |
| GET | `/api/reportes/ventas` | Ventas por rango | Admin/cajero sujeto a ruta |
| POST | `/api/pagos/stripe/webhook` | Webhook firmado Stripe | Firma válida, no JWT |

### B. Microservicios (JWT, detrás de Nginx)

| Método | URL | Servicio | Tipo |
|---|---|---|---|
| POST | `/api/auth/login` | Autenticación | Obtener JWT |
| POST | `/api/auth/register` | Autenticación | Crear usuario de demo según validación |
| POST | `/api/auth/verify` | Autenticación | Validar token |
| GET | `/api/inventario/productos` | Inventario | Catálogo de demostración |
| POST | `/api/inventario/productos` | Inventario | Crear en demo |
| PUT | `/api/inventario/productos/{id}` | Inventario | Editar demo |
| DELETE | `/api/inventario/productos/{id}` | Inventario | Desactivar demo |
| GET | `/api/inventario/variantes/{id}` | Inventario | Variante demo |
| GET | `/api/inventario/pos/resumen` | Inventario | Resumen de PROYECCIÓN Laravel |
| GET | `/api/inventario/pos/productos` | Inventario | Productos reales proyectados |
| GET | `/api/ventas` | Ventas | Ventas demo |
| POST | `/api/ventas` | Ventas | Venta de demostración y descuento en inventario demo |
| GET | `/api/ventas/pos/resumen` | Ventas | Ventas POS y pedidos proyectados |
| GET | `/api/reportes/pos/resumen` | Reportes | Consolidado proyectado de inventario y ventas |
| GET | `/api/reportes/resumen` | Reportes | Consolidado de datos demo |

Los endpoints `/api/inventario/pos/sincronizar` y `/api/ventas/pos/sincronizar*` son **internos**; exigen `X-Internal-Token` y no deben invocarse desde el navegador. No publicar ni registrar el token en capturas.

## Pruebas

```powershell
# Asegura que Docker Desktop esté iniciado.
.\distributed\scripts\prueba-distribuida.ps1
.\distributed\scripts\probar-sincronizacion-pos.ps1
```

Respuestas JSON y HTTP: 200 lectura/actualización, 201 creación, 401 token ausente/inválido, 403 permisos, 404 ruta/recurso, 409 insuficiencia de stock, 422 validación, 503 dependencia no disponible (dependiendo del endpoint).

**Cautela:** los servicios deben probarse en ejecución y no solo con `route:list` del Laravel. La ruta `/api/reportes/ventas` de Laravel y las rutas de informes del microservicio son diferentes.
