# SIS_ROPA — Control de entrega UMG (28/10/2026)

**Un solo producto web**, con POS para ventas presenciales, tienda en línea y servicios REST para demostración académica. Se recomienda presentar una URL pública, repositorio de GitHub, scripts SQL, diagramas, capturas y explicación técnica.

## Proyecto 1 — POS distribuido (15 puntos)

| Requisito del documento | Estado | Evidencia / límite |
|---|---|---|
| POS y gestión de productos | Probado localmente | Pantallas `/pos`, `/productos`, variantes y compras |
| Docker Compose y Nginx | Probado por estudiante | `docker compose ... ps -a`, gateway 8090 |
| Servicios auth, inventario, ventas y reportes | Probados como contenedores | `prueba-distribuida.ps1` (venta DEMO) |
| JWT y comunicación REST | Prueba básica superada | `prueba-distribuida.ps1` |
| Replicación Master/Replica MariaDB | Comprobada activa | `Slave_IO_Running=Yes`; `Slave_SQL_Running=Yes`; retraso=0 en prueba |
| Sincronización POS → APIs | Prueba básica superada | `probar-sincronizacion-pos.ps1`: ventas y stock de proyección |
| Operaciones comerciales escritas directamente por servicios independientes | **Pendiente** | Laravel conserva escritura del POS y del inventario real; APIs reciben proyecciones. No afirmar cumplimiento estricto |
| Mínimo 8 endpoints REST | Implementados en código | Ver `docs/API_REST_SISROPA.md`; probar en vivo |
| APIs externas (mínimo 2) | Probadas localmente | Stripe prueba, QR y OpenWeather en Docker; falta documentar capturas finales |
| Acceso web público y bases en la nube | Parcial | Railway anterior existe, pero arquitectura distribuida solo probada localmente |
| Documento PDF / capturas / demo 10 min | Documentación base aquí | Preparar las capturas y exportar a PDF |

## Proyecto 2 — E-commerce con APIs (15 puntos)

| Requisito | Estado | Evidencia / límite |
|---|---|---|
| Catálogo público, carrito, checkout, cuenta cliente | Probado localmente | `/tienda`, `/carrito`, `/checkout`, `/pedidos` |
| Pagos Stripe/PayPal Sandbox | Stripe probado localmente | Captura de “Pagado (prueba)” en pedido |
| Mínimo 3 APIs externas visibles | Probadas localmente | Stripe de prueba, QR y OpenWeather mostrado en Docker (15 °C en captura); falta evidencia formal |
| API REST ≥6 endpoints | Implementados en código | Ver documento API |
| ≥20 productos | Verificado localmente | Seeder de 21 productos ejecutado, dashboard y proyección 21 productos |
| Imágenes reales de 20 productos | **Pendiente** | Las imágenes no se generan automáticamente; cargar fotografías pertinentes |
| Panel admin, Chart.js, PDF | Parcial | Dashboard, reportes y comprobante imprimible; verificar gráfico y PDF reporte |
| Cloudinary / imágenes en nube | **Código implementado (Actualización 08), no probado con cuenta** | Subida opcional firmada para foto principal y por color; requiere credenciales, prueba y fotos reales |
| Publicación y documento PDF | **Pendiente** | La prueba en `127.0.0.1:8090` no demuestra hosting público del Docker |
| Negocio distinto al del Proyecto 1 | **Conflicto documental** | Proyecto 2 exige negocio diferente; SIS_ROPA mantiene ropa. Un solo sistema no satisface literalmente esta condición sin autorización docente; penalización señalada en documento: -2 pts |

## Para la demostración de clase

1. Abrir dashboard y CRUD de productos con talla/color, marcas y fotos por color.
2. Mostrar pedido público y venta en caja sobre un mismo inventario Laravel.
3. Mostrar pago con Stripe **solo prueba**, QR y cambios de estado del pedido.
4. Ejecutar `docker compose --env-file .env.distribuido -f docker-compose.distribuido.yml ps -a`.
5. Ejecutar `./distributed/scripts/prueba-distribuida.ps1` para flujo REST DEMO con JWT.
6. Ejecutar `./distributed/scripts/probar-sincronizacion-pos.ps1` para proyecciones del POS REAL.
7. Mostrar `SHOW SLAVE STATUS\G` de MariaDB réplica (IO/SQL Yes, errores vacíos).
8. Explicar que Laravel todavía hace escrituras comerciales y servicios consultan una proyección. No presentar los dos mecanismos como si fueran el mismo flujo.

**Nota fiscal:** el comprobante imprimible interno de SIS_ROPA no es una factura FEL de SAT.

**Seguridad:** nunca entregar archivos `.env`, contraseñas reales, claves Stripe, APP_KEY, secretos JWT ni volúmenes de base de datos en el repositorio.

## Actualización 08 — evidencias adicionales

- Captura del dashboard con OpenWeather habilitado en Docker: 15 °C, humedad 89 % (valor mostrado en el momento de la prueba).
- Diagnóstico `verificar-entorno.ps1`: HTTP 200, 21 productos, 225 existencias proyectadas, una venta POS y dos pedidos web.
- Fotos Cloudinary disponibles en código con firma servidor. **Pendiente**: cargar y comprobar imágenes reales con cuenta del estudiante; no se hizo una llamada externa desde este entorno.
- Se sustituye el icono de clima remoto roto por un símbolo local; los datos del tiempo siguen viniendo de OpenWeather.
