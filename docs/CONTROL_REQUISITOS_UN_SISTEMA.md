# SIS_ROPA — un único sistema para dos conjuntos de requisitos

## Decisión de arquitectura

Una sola aplicación que el usuario ve, una única dirección por entorno (`http://localhost:8090` en Docker), con interfaces de caja física y comercio electrónico. La parte técnica usa cuatro microservicios independientes (autenticación, inventario, ventas y reportes) comunicados vía REST, un API Gateway Nginx y bases separadas. No significa dos sitios web o dos repositorios.

**Esta actualización es una base de microservicios ejecutables y pruebas aisladas, no la integración completa con el POS Laravel.** Mientras las acciones de la interfaz Laravel sigan escribiendo directamente a su `pos_ropa`, falta delegar esas operaciones a los servicios para cumplir integralmente la arquitectura distribuida. No se debe defender como trabajo final terminado todavía.

## Matriz académica verificada contra los dos documentos

| Exigencia | Situación tras esta actualización |
|---|---|
| Interfaz POS y comercio electrónico | Ya existe y se ha probado en ambiente local |
| Roles admin / cajero / cliente | Existentes en Laravel; auth-service tiene cuentas de demo aisladas |
| Variantes de talla y color | Existentes en Laravel; inventario-service incluye muestras aisladas |
| Carrito, checkout y pedidos | Existentes en Laravel |
| Stripe modo prueba y QR | Primer pago de prueba probado por usuario; revisar webhook |
| Mínimo 3 APIs externas visibles (Proyecto 2) | Stripe + QR + clima candidatos; verificar funcionamiento visible/documentado y exacta consideración de API externa |
| API propia 8 endpoints / REST JSON | Base API Laravel + rutas nuevas; falta inventario formal y pruebas/documentación integral |
| 4 microservicios independientes / gateway Nginx | Código e infraestructura incluidos; pendiente levantar Docker y conectar flujos UI |
| Docker Compose | Archivo incluido; pendiente ejecución real |
| JWT entre microservicios | Auth, inventario, ventas y reportes incorporados; pendiente prueba real |
| MariaDB Master-Slave | Init y script incluidos; pendiente verificar dos hilos en Docker |
| BD aislada por servicio | Cuatro esquemas, cuatro usuarios y privilegios separados incluidos |
| Hosting accesible al catedrático | Railway anterior contiene la versión monolítica; **esta arquitectura no está desplegada** |
| SQL, README, diagramas y PDF | Parte del código está; falta preparar entregables finales |
| E-Commerce de negocio diferente al del POS | **Conflicto no resuelto**: el Proyecto 2 prohíbe repetir el tipo de negocio del Proyecto 1 |

## Incompatibilidad explícita entre documentos

El Proyecto 1 permite **Tienda de Ropa y Accesorios**. El Proyecto 2 solicita un negocio **DISTINTO** del del Proyecto 1 y lista Restaurante, Supermercado, Electrónica, Veterinaria o Panadería. También penaliza repetir el tipo de negocio (-2 puntos). **Poner tienda en línea de ropa dentro de SIS_ROPA no satisface literalmente esa cláusula**, aunque el sistema implemente todas las funciones técnicas.

Solución técnicamente posible con **un solo sistema**: añadir un segundo catálogo/rubro (por ejemplo Electrónica) a la misma plataforma y presentar el caso E-Commerce usando el catálogo de Electrónica, mientras POS usa Ropa. Esto requeriría mostrar dos rubros claramente separados y podría aún necesitar aceptación docente sobre la interpretación de «negocio diferente». Para no arriesgar puntos conviene conseguir esa aceptación o una excepción escrita. No hace falta desarrollar un segundo repositorio.

## Próximos cambios imprescindibles

1. Integrar inventario y venta real del POS Laravel con servicios, sin dos fuentes de stock.
2. Probar Docker/replicación, después desplegarlo en hosting que soporte los contenedores (no asumir que el Railway actual corresponde automáticamente).
3. Completar OAuth/JWT/casos de prueba, documentación de al menos 8 endpoints y diagramas.
4. Resolver claramente el rubro diferente del E-Commerce sin crear una segunda app.
5. Probar pagos sandbox y las 3 APIs externas visibles, agregar datos de prueba y capturas.

## Límite conocido de este ejemplo

En la demostración REST distribuida, cada venta contiene una variante, y la compensación ante fallos entre ventas/inventario es básica. No usar este camino como caja de producción ni como reemplazo inmediato del POS existente hasta completar manejo de transacciones distribuidas e idempotencia.
