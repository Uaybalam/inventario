<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use \App\Models\ProductoSku;
use \App\Models\ubicacion;
use \App\Models\Responsable;

class Inventario extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_producto_sku',
        'id_inventario',
        'cantidad',
        'fecha_actualizacion',
    ];

    public function producto()
    {
        return $this->belongsTo(ProductoSku::class, 'id_producto_sku');
    }

    // (Opcionales si decides agregar estos campos)
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion');
    }

    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'id_responsable');
    }
}
