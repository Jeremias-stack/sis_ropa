# Pruebas manuales de aceptación de SIS_ROPA

Trabajar exclusivamente en Docker local (puerto 8090) o una BD de pruebas. No usar datos de producción.

| Código | Prueba | Resultado esperado | Realizado |
|---|---|---|---|
| P01 | Administrador crea producto con M/negro=10 y S/rojo=5 | Un producto, dos variantes, stock 15 | ☐ |
| P02 | Cajero vende M/negro cantidad 2 | M/negro=8, S/rojo=5; venta aparece en reportes REST | ☐ |
| P03 | Admin registra compra y recibe mercancía parcialmente | Aumenta solo la combinación recibida; resta pendiente | ☐ |
| P04 | Cliente compra 1 unidad por web | Pedido registrado y stock baja una vez | ☐ |
| P05 | Cliente paga vía Stripe prueba | Pago pagado (prueba), pedido continúa pendiente hasta envío | ☐ |
| P06 | Admin cambia estado de pedido | Enviado / entregado; estado del pago se mantiene | ☐ |
| P07 | Cajero intenta acceder a `/usuarios` | Acceso rechazado | ☐ |
| P08 | Producto disponible en varios colores | Fotos cambian por color, tallas conservan stock propio | ☐ |
| P09 | Exporta recibo a PDF desde navegador | Muestra logo, variante, IVA, total y leyenda de comprobante interno | ☐ |
| P10 | APIs distribuidas | Scripts de prueba básica y proyección terminan sin errores | ☐ |
| P11 | Replicación MariaDB | IO/SQL Yes, errores vacíos, datos de prueba replicados | ☐ |
| P12 | Sembrar productos académicos | ≥20 productos, sin duplicar o sobrescribir los existentes | ☐ |
| P13 | Sin clave de Stripe | Checkout aún admite pago contra entrega | ☐ |
| P14 | API externa clima | Muestra datos actualizados, no mensaje «no configurado» | ☐ |

**No ejecutes** `migrate:fresh`, `db:wipe`, `docker compose down -v` ni el seeder de usuarios en producción para pruebas.
