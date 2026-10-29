<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Producto extends Model
{
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'precio',
        'stock',
        'categoria_id',
        'marca_id',
        'talla',
        'color',
        'genero',
        'imagen_url',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class);
    }

    public function imagenesColores()
    {
        return $this->hasMany(ProductoImagenColor::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVenta::class);
    }

    // Alerta visual de stock bajo (< 5 unidades), pedido en la guía.
    public function getStockBajoAttribute(): bool
    {
        return $this->stock < 5;
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function (Builder $q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
                ->orWhere('codigo', 'like', "%{$termino}%")
                ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', "%{$termino}%"))
                ->orWhereHas('variantes', fn ($v) => $v->whereHas('talla', fn ($t) => $t->where('nombre', 'like', "%{$termino}%"))
                    ->orWhereHas('color', fn ($c) => $c->where('nombre', 'like', "%{$termino}%")));
        });
    }
}
