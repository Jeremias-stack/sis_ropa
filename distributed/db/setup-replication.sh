#!/bin/sh
set -eu
: "${DB_ROOT_PASSWORD:?Se requiere DB_ROOT_PASSWORD}"
MASTER="mariadb -h db-master -uroot -p${DB_ROOT_PASSWORD} --batch --skip-column-names"
REPLICA="mariadb -h db-replica -uroot -p${DB_ROOT_PASSWORD}"
# Se utiliza la posición actual porque ambas bases reciben la misma estructura
# al inicializarse. Inicia la réplica ANTES de introducir nuevos datos.
STATUS=$($MASTER -e 'SHOW MASTER STATUS')
FILE=$(printf '%s\n' "$STATUS" | awk 'NR==1{print $1}')
POSITION=$(printf '%s\n' "$STATUS" | awk 'NR==1{print $2}')
if [ -z "$FILE" ] || [ -z "$POSITION" ]; then echo "No se encontró binlog en master"; exit 1; fi
$REPLICA -e "STOP SLAVE; CHANGE MASTER TO MASTER_HOST='db-master', MASTER_PORT=3306, MASTER_USER='replicador', MASTER_PASSWORD='SoloDemoRepl_2026!', MASTER_LOG_FILE='$FILE', MASTER_LOG_POS=$POSITION, MASTER_CONNECT_RETRY=5; START SLAVE;"
echo "Replicación iniciada desde $FILE:$POSITION"
$REPLICA -e 'SHOW SLAVE STATUS\G' | grep -E 'Slave_IO_Running:|Slave_SQL_Running:|Last_IO_Error:|Last_SQL_Error:' || true
