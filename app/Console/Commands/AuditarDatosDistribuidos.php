<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Contrasta la base comercial (master) contra la réplica de MariaDB.
 * Solo consulta; no modifica inventario, ventas ni pedidos.
 */
class AuditarDatosDistribuidos extends Command
{
    protected $signature = 'distribuido:auditar-datos {--json : Devuelve únicamente JSON para el script de PowerShell}';
    protected $description = 'Comprueba los datos comerciales en MariaDB master y réplica, sin escrituras';

    private function resumen(ConnectionInterface $db): array
    {
        return [
            'productos' => (int) $db->table('productos')->count(),
            'categorias' => (int) $db->table('categorias')->count(),
            'existencias' => (int) $db->table('producto_variantes')->sum('stock'),
            'ventas_pos' => (int) $db->table('ventas')->count(),
            'pedidos_web' => (int) $db->table('pedidos')->count(),
        ];
    }

    public function handle(): int
    {
        try {
            if (config('database.default') !== 'mysql') {
                throw new \RuntimeException('Ejecuta esta auditoría dentro del Docker de SIS_ROPA con MySQL.');
            }

            $principal = $this->resumen(DB::connection('mysql'));

            // Conexión temporal de SOLO LECTURA a la réplica. Usa el mismo usuario
            // y credenciales ya configurados en Docker; no requiere nuevas claves.
            $conexion = config('database.connections.mysql');
            $conexion['url'] = null;
            $conexion['host'] = 'db-replica';
            $conexion['sticky'] = false;
            unset($conexion['read'], $conexion['write']);
            Config::set('database.connections.sisropa_auditoria_replica', $conexion);
            DB::purge('sisropa_auditoria_replica');
            $replica = $this->resumen(DB::connection('sisropa_auditoria_replica'));

            $iguales = $principal === $replica;
            $catalogoCompleto = $principal['productos'] >= 20;
            $resultado = [
                'correcto' => $iguales && $catalogoCompleto,
                'master_y_replica_iguales' => $iguales,
                'minimo_20_productos' => $catalogoCompleto,
                'master' => $principal,
                'replica' => $replica,
                'nota' => 'La auditoría verifica réplica y tablas comerciales; NO prueba que el POS escriba mediante microservicios.',
            ];

            if ($this->option('json')) {
                $this->line(json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
            } else {
                $this->table(['Dato', 'Master', 'Réplica'], array_map(
                    fn ($nombre) => [$nombre, $principal[$nombre], $replica[$nombre]],
                    array_keys($principal)
                ));
                $this->line($iguales ? 'OK: los datos de master y réplica coinciden.' : 'ERROR: hay diferencias entre master y réplica.');
                $this->line($catalogoCompleto ? 'OK: se cumple el mínimo de 20 productos.' : 'ERROR: hay menos de 20 productos.');
            }

            return $resultado['correcto'] ? self::SUCCESS : self::FAILURE;
        } catch (Throwable $e) {
            if ($this->option('json')) {
                $this->line(json_encode(['correcto' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE));
            } else {
                $this->error('No se pudo completar la auditoría: '.$e->getMessage());
            }
            return self::FAILURE;
        }
    }
}
