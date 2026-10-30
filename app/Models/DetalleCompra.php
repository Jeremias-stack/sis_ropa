<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCompra extends Model
{
    protected $table = 'detalle_compras';
    protected $fillable = ['compra_id', 'producto_variante_id', 'cantidad', 'cantidad_recibida', 'costo_unitario', 'subtotal'];
    protected function casts(): array { return ['costo_unitario' => 'decimal:2', 'subtotal' => 'decimal:2']; }
    public function compra() { return $this->belongsTo(Compra::class); }
    public function variante() { return $this->belongsTo(ProductoVariante::class, 'producto_variante_id'); }
}
