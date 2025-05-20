<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use \App\Models\ProductoSku;
use \App\Models\Ubicacion;
use \App\Models\Responsable;
use \App\Models\Estado;
use \App\Models\User;

class Inventario extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_inventario';

    protected $fillable = [
        'id_producto_sku',
        'id_inventario',
        'cantidad',
        'fecha_actualizacion',
        'id_responsable',
        'id_ubicacion',
        'id_estado',
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
    public function usuario()
{
    return $this->belongsTo(User::class, 'id_usuario');
}
public function estado()
{
    return $this->belongsTo(\App\Models\Estado::class, 'id_estado');
}
}
