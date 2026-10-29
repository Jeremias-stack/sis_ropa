<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = ['proveedor_id', 'usuario_id', 'referencia', 'fecha', 'total', 'estado', 'observaciones'];
    protected function casts(): array { return ['fecha' => 'date', 'total' => 'decimal:2']; }
    public function proveedor() { return $this->belongsTo(Proveedor::class); }
    public function usuario() { return $this->belongsTo(User::class); }
    public function detalles() { return $this->hasMany(DetalleCompra::class); }
}
