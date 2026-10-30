<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalle_pedidos';
    protected $fillable = ['pedido_id','producto_variante_id','nombre_producto','talla','color','cantidad','precio_unitario','subtotal'];
    protected function casts(): array { return ['precio_unitario'=>'decimal:2','subtotal'=>'decimal:2']; }
    public function pedido() { return $this->belongsTo(Pedido::class); }
    public function variante() { return $this->belongsTo(ProductoVariante::class,'producto_variante_id'); }
}
