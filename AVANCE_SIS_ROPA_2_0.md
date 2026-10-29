# SIS_ROPA 2.0 — Primera entrega de desarrollo

Esta carpeta contiene la **base de código del sistema**, conservando Laravel 13, PHP 8.4, las vistas originales y las funcionalidades anteriores. Se trabajó sobre una copia del ZIP entregado; **no se modificó Railway ni su MySQL**.

## Incluido y programado en esta etapa

- Migración aditiva de `marcas`, `tallas`, `colores` y `producto_variantes`.
- Las existencias de los productos anteriores se migran a **una variante inicial por producto**, sin eliminar productos ni historial de ventas.
- Formulario de productos con varias combinaciones talla/color/cantidad en un solo producto, total de unidades y catálogo administrable.
- Consultas por nombre, código, marca, talla y color.
- Selector de talla, color y cantidad antes de agregar al carrito POS.
- Venta transaccional que descuenta existencias por variante y también actualiza el stock total del producto.
- Registro en cada detalle de venta de la talla y el color vendidos, mostrados en el comprobante.
- Nuevo registro público de usuarios con rol **cliente**, en lugar de cajero; bloqueo del panel administrativo a clientes.
- Catálogo público inicial de productos `/tienda` (aún **sin** carrito y checkout en línea).
- Compatibilidad básica de los endpoints REST y seeder de productos con la tabla de variantes.
- Pruebas de regresión en `tests/Feature/InventarioVariantesTest.php`.

## Falta por implementar (no se declara completado)

**En SIS_ROPA:** administración de permisos finos y empleados; proveedores y órdenes de compra; cupones, descuentos avanzados, cambios y devoluciones; pagos mixtos; caja por turnos; carrito y checkout público; pedidos, seguimiento, favoritos y reseñas; PDF real con logotipo y diseño final; funcionalidades opcionales acordadas para la entrega escolar.

**Proyecto 1:** microservicios desplegados independientemente, JWT entre servicios, API Gateway Nginx, Docker Compose completo y replicación MariaDB Master-Slave; además, verificar las integraciones de APIs y pagos sandbox.

**Proyecto 2:** el documento exige otro tipo de negocio diferente del POS de ropa, además del E-Commerce con pagos sandbox y 3 APIs externas. Un catálogo público de ropa **no** cumple por sí solo el Proyecto 2.

## Antes de desplegar (importante)

1. **Respaldar la base de datos de Railway** (estructura y registros) y conservar el ZIP original.
2. Crear una rama nueva a partir de `jobat-dev` y copiar allí **solo** los archivos de esta entrega después de comparar los cambios. No reemplazar directamente producción.
3. En local, instalar dependencias de tu proyecto: `composer install`, `npm install`. Configurar `.env` local desde `.env.example` con una base de datos de pruebas.
4. Ejecutar primero `php artisan migrate --force` **solo contra la base de pruebas**. La migración crea las nuevas tablas y convierte los productos anteriores en variantes sin borrar las tablas existentes.
5. Ejecutar `php artisan test --filter=InventarioVariantesTest` y probar manualmente: registrar producto con 2 combinaciones; agregar al POS talla M color Negro; vender 2 unidades; verificar que solamente esa variante baja; editar stock; consultar comprobante; probar que cliente no acceda al panel.
6. Ejecutar `npm run build`. Cuando la copia de pruebas funcione, planificar despliegue a Railway con copia de seguridad previa.
7. No ejecutar `php artisan migrate:fresh`, `php artisan db:wipe` ni `db:seed` indiscriminadamente en la base de producción.

Este ZIP **no incluye** `.env`, contraseñas, datos de la base de producción, `.git`, `vendor`, ni `node_modules`. Para ejecutar necesitas tus dependencias y un `.env` local creado por ti.

## Comprobaciones realizadas en este entorno

- Sintaxis PHP de modelos, controladores, rutas y migraciones: **correcta** (`php -l`).
- Sintaxis de JavaScript del POS: **correcta** (`node --check`).
- Compilación estática y sintaxis PHP de las vistas Blade intervenidas: **correcta**.
- Laravel reconoció rutas de catálogo, productos, tienda y autenticación.
- **No fue posible ejecutar las migraciones ni las pruebas con base de datos**: este entorno de PHP carece de drivers PDO MySQL/SQLite. Por ello, la versión requiere validación local sobre base de pruebas antes de desplegar.
