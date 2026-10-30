#!/bin/sh
set -eu
# Las variables deben estar configuradas en Railway, no en el repositorio.
: "${APP_DB_PASSWORD:?Falta APP_DB_PASSWORD}"
: "${AUTH_DB_PASSWORD:?Falta AUTH_DB_PASSWORD}"
: "${INVENTORY_DB_PASSWORD:?Falta INVENTORY_DB_PASSWORD}"
: "${SALES_DB_PASSWORD:?Falta SALES_DB_PASSWORD}"
: "${REPORTS_DB_PASSWORD:?Falta REPORTS_DB_PASSWORD}"
: "${AUTH_ADMIN_HASH:?Falta AUTH_ADMIN_HASH (bcrypt)}"
# Para evitar inyección SQL al interpolar valores, las claves son solo alfanuméricas.
for k in "$APP_DB_PASSWORD" "$AUTH_DB_PASSWORD" "$INVENTORY_DB_PASSWORD" "$SALES_DB_PASSWORD" "$REPORTS_DB_PASSWORD"; do
    case "$k" in *[!a-zA-Z0-9]*|'') echo 'Las contraseñas de DB deben ser solo alfanuméricas' >&2; exit 1 ;; esac
    [ "${#k}" -ge 24 ] || { echo 'Las claves de DB requieren al menos 24 caracteres' >&2; exit 1; }
done
case "$AUTH_ADMIN_HASH" in '$2y$'*|'$2a$'*|'$2b$'*) : ;; *) echo 'AUTH_ADMIN_HASH no es bcrypt' >&2; exit 1 ;; esac
# bcrypt: 60 caracteres, sin comillas; el hash nunca se imprime.
[ "${#AUTH_ADMIN_HASH}" -eq 60 ] || { echo 'AUTH_ADMIN_HASH incorrecto' >&2; exit 1; }
mariadb --protocol=socket -uroot -p"${MARIADB_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS pos_ropa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS auth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS inventario_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS ventas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS reportes_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'laravel_app'@'%' IDENTIFIED BY '${APP_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'auth_app'@'%' IDENTIFIED BY '${AUTH_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'inventario_app'@'%' IDENTIFIED BY '${INVENTORY_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'ventas_app'@'%' IDENTIFIED BY '${SALES_DB_PASSWORD}';
CREATE USER IF NOT EXISTS 'reportes_app'@'%' IDENTIFIED BY '${REPORTS_DB_PASSWORD}';
GRANT ALL PRIVILEGES ON pos_ropa.* TO 'laravel_app'@'%';
GRANT ALL PRIVILEGES ON auth_db.* TO 'auth_app'@'%';
GRANT ALL PRIVILEGES ON inventario_db.* TO 'inventario_app'@'%';
GRANT ALL PRIVILEGES ON ventas_db.* TO 'ventas_app'@'%';
GRANT ALL PRIVILEGES ON reportes_db.* TO 'reportes_app'@'%';
FLUSH PRIVILEGES;
SQL
# En la primera inicialización, el esquema se ejecuta después en 20-schema.sql.
# Semilla administrativa tras esquema, en 30-admin.sh.
