<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory; 
class Transaccion extends Model
{
    use HasFactory;
    protected $table = 'transacciones';
    protected $primaryKey = 'id_transaccion';

    public function producto()
    {
        return $this->belongsTo(ProductoSku::class, 'id_producto_sku');
    }
}
