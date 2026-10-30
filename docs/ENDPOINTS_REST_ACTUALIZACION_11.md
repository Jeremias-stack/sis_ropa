# Actualización 11: endpoints del servicio distribuido

Estos endpoints están detrás de Nginx en `/api/*` (puerto 8090 local) y requieren JWT emitido por **Auth-service**; NO son equivalentes a los tokens Laravel Sanctum.

| Método | Ruta | Origen de datos | Acceso |
|---|---|---|---|
| GET | `/api/ventas/pos/detalle?desde=YYYY-MM-DD&hasta=YYYY-MM-DD` | `ventas_db.ventas_pos` proyectada desde Laravel | Administrador |
| GET | `/api/ventas/{id}/factura` | Primero venta POS proyectada; si no existe, venta distribuida de demostración | Admin/cajero |
| GET | `/api/reportes/pos/ventas?desde=YYYY-MM-DD&hasta=YYYY-MM-DD` | Reportes-service consulta Sales-service vía REST | Administrador |
| GET | `/api/reportes/top-productos` | Reportes consulta Sales e Inventory por REST | Administrador |

`/api/reportes/ventas` se reserva en Nginx para la **API Laravel Sanctum** existente; el servicio Reports internamente también reconoce esa ruta, pero por gateway se debe usar `/api/reportes/pos/ventas` con JWT.

El reporte y los productos más vendidos son **solo de ventas POS proyectadas**. No incluyen pedidos web; estos se consultan en `/api/ventas/pos/resumen`.

**Límite explícito:** `ventas/pos/detalle` devuelve hasta 2000 ventas y `truncado=true` si supera esa cifra; los reportes derivados también informan su parcialidad. Para producción real habría que paginar/agrupación completa en Sales-service.

**Factura API:** devuelve JSON con total, líneas, fecha, número y dirección externa del QR. El dato `iva` de la proyección POS es `null` por no estar disponible en la proyección; para IVA exacto se utiliza la factura original de Laravel, no un cálculo supuesto. No genera PDF binario desde este endpoint; la impresión/guardado PDF se realiza desde la interfaz Laravel.

**Prueba no destructiva:** `./distributed/scripts/prueba-entrega-endpoints.ps1`; genera evidencia en `docs/evidencias/` sin crear nuevas ventas.

**Precaución:** no exponer `/api/ventas/pos/sincronizar*` ni `/api/inventario/pos/sincronizar` como acciones para usuarios externos; estas rutas requieren un token interno distinto de JWT.
