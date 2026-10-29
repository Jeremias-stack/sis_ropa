<?php

namespace App\Console\Commands;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\SincronizacionDistribuida;
use Illuminate\Console\Command;

class SincronizarDistribuido extends Command
{
    protected $signature = 'distribuido:sincronizar {--solo-productos : No enviar ventas}';
    protected $description = 'Sincroniza las proyecciones REST del inventario y las ventas Laravel';

    public function handle(SincronizacionDistribuida $sync): int
    {
        if (!$sync->habilitada()) {
            $this->error('No activada. Usa el contenedor Docker con DISTRIBUTED_SYNC_ENABLED=true.');
            return self::FAILURE;
        }
        $bien = 0; $errores = 0;
        Producto::query()->orderBy('id')->chunkById(100, function ($productos) use ($sync, &$bien, &$errores) {
            foreach ($productos as $p) {
                try { $sync->producto((int) $p->id); $bien++; }
                catch (\Throwable $e) { $this->error('Producto '.$p->id.': '.$e->getMessage()); $errores++; }
            }
        });
        $this->info('Productos proyectados: '.$bien);
        if (!$this->option('solo-productos')) {
            $ventas = 0;
            Venta::query()->orderBy('id')->chunkById(100, function ($grupo) use ($sync, &$ventas, &$errores) {
                foreach ($grupo as $v) {
                    try { $sync->venta((int) $v->id); $ventas++; }
                    catch (\Throwable $e) { $this->error('Venta '.$v->id.': '.$e->getMessage()); $errores++; }
                }
            });
            $this->info('Ventas POS proyectadas: '.$ventas);
            $pedidos = 0;
            Pedido::query()->orderBy('id')->chunkById(100, function ($grupo) use ($sync, &$pedidos, &$errores) {
                foreach ($grupo as $p) {
                    try { $sync->pedido((int) $p->id); $pedidos++; }
                    catch (\Throwable $e) { $this->error('Pedido '.$p->id.': '.$e->getMessage()); $errores++; }
                }
            });
            $this->info('Pedidos web proyectados: '.$pedidos);
        }
        if ($errores) $this->error('Errores: '.$errores.'. Repite el comando cuando el servicio esté disponible.');
        return $errores ? self::FAILURE : self::SUCCESS;
    }
}
