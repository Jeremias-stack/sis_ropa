#!/bin/sh
set -eu
: "${AUTH_ADMIN_HASH:?Falta AUTH_ADMIN_HASH}"
for hash in "$AUTH_ADMIN_HASH" "${AUTH_CASHIER_HASH:-}"; do
    [ -n "$hash" ] || continue
    case "$hash" in '$2y$'*|'$2a$'*|'$2b$'*) : ;; *) echo 'Se necesita un hash bcrypt' >&2; exit 1 ;; esac
    case "$hash" in *"'"*) echo 'Hash inválido' >&2; exit 1 ;; esac
    [ "${#hash}" -eq 60 ] || { echo 'El hash bcrypt debe tener 60 caracteres' >&2; exit 1; }
done
mariadb --protocol=socket -uroot -p"${MARIADB_ROOT_PASSWORD}" <<SQL
INSERT IGNORE INTO auth_db.usuarios (nombre,correo,password_hash,rol)
VALUES ('Administrador de evaluación','admin@demo.local','${AUTH_ADMIN_HASH}','administrador');
SQL
if [ -n "${AUTH_CASHIER_HASH:-}" ]; then
mariadb --protocol=socket -uroot -p"${MARIADB_ROOT_PASSWORD}" <<SQL
INSERT IGNORE INTO auth_db.usuarios (nombre,correo,password_hash,rol)
VALUES ('Cajero de evaluación','cajero@demo.local','${AUTH_CASHIER_HASH}','cajero');
SQL
fi
