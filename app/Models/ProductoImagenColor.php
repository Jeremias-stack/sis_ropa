<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagenColor extends Model
{
    protected $table = 'producto_imagenes_colores';
    protected $fillable = ['producto_id', 'color_id', 'ruta'];

    public function producto() { return $this->belongsTo(Producto::class); }
    public function color() { return $this->belongsTo(Color::class); }
}
