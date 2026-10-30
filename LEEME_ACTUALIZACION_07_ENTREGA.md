# SIS_ROPA 2.0 — Actualización 07 · Preparación de entrega

Esta actualización **NO crea otro sistema**. Se aplica encima de las actualizaciones 01–06. No modifica las bases de datos ni los datos al copiar archivos.

## Archivos a reemplazar

- `database/seeders/ProductoSeeder.php`: amplía de 9 a **21 productos de prueba**, sin duplicar códigos ya existentes ni borrar existencias.
- `resources/views/ventas/show.blade.php`: logotipo SIS_ROPA, campos de negocio configurables y leyenda de comprobante interno.
- `resources/css/factura.css`: formato más legible al guardar/imprimir en PDF.

## Archivos nuevos (crear si no existen)

- `config/tienda.php`: nombre, dirección, teléfono, NIT y logo configurables. Todos los campos tienen valores predeterminados.
- `public/imagenes/logo-sisropa.svg`: logo minimalista original del recibo.
- `docs/ENTREGA_UMG_SISROPA.md`: estado académico honesto de ambos proyectos y pendientes.
- `docs/API_REST_SISROPA.md`: tabla de endpoints Laravel y microservicios.
- `docs/ARQUITECTURA_Y_DEMO.md`: diagrama Mermaid y guion de 10 minutos.
- `docs/PRUEBAS_DE_ENTREGA.md`: lista de pruebas para tomar capturas.
- `distributed/scripts/verificar-entorno.ps1`: comprobación de Docker sin escribir ventas ni imprimir secretos.

## Instalar sin romper lo existente

1. Respaldar la carpeta `sis_ropa` y exportar la BD de pruebas.
2. Extraer el ZIP y copiar las carpetas `database`, `resources`, `config`, `public`, `docs` y `distributed` en la raíz de `C:\Users\jobat\sis_ropa`, **combinando** carpetas. Reemplazar solo los 3 archivos marcados arriba. **No copiar .env ni .env.distribuido** (este ZIP no los contiene).
3. Con Docker Desktop abierto, en PowerShell desde la raíz del proyecto:

```powershell
docker compose --env-file .env.distribuido -f docker-compose.distribuido.yml up -d --build
docker compose --env-file .env.distribuido -f docker-compose.distribuido.yml exec app php artisan optimize:clear
docker compose --env-file .env.distribuido -f docker-compose.distribuido.yml exec app php artisan db:seed --class=ProductoSeeder --force
.\distributed\scripts\probar-sincronizacion-pos.ps1
```

4. Abrir `http://127.0.0.1:8090/productos` y comprobar que hay **21** productos (si antes había solo 9; si has creado otros, podrían ser más). Abrir factura de una venta: `Guardar como PDF / Imprimir` → seleccionar **Guardar como PDF** en Chrome.
5. La configuración del logotipo puede personalizarse en `.env.distribuido` con `TIENDA_NOMBRE`, `TIENDA_DIRECCION`, `TIENDA_TELEFONO`, `TIENDA_NIT` y `TIENDA_LOGO`, pero para que Docker las reciba tendrás que agregarlas al bloque `environment` del servicio `app` del archivo Compose. **Sin cambiar nada, utiliza los valores predeterminados.**

## Qué no se ha verificado automáticamente

- No se ejecutaron migraciones ni seeders en tu Docker Windows desde este entorno.
- El botón usa impresión de navegador para guardar un PDF, **no** una biblioteca de generación PDF del servidor.
- Las fotografías reales para 20 productos y Cloudinary siguen pendientes.
- Los microservicios comerciales siguen usando proyecciones de lectura sincronizadas desde Laravel; el POS real no delega sus escrituras a servicios independientes. No afirmar cumplimiento estricto del punto de arquitectura.
- El Proyecto 2 pide negocio diferente del Proyecto 1. Un solo negocio de ropa incumple literalmente esa instrucción; el docente tendría que aceptar una excepción o variación de rubro.

## Seguridad y compatibilidad

No usar `migrate:fresh`, `db:wipe`, ni `docker compose down -v`. No publicar en GitHub archivos `.env`, claves Stripe ni variables secretas. El texto «Comprobante interno» indica que el recibo NO es factura fiscal FEL autorizada.
