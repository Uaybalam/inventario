<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Categoria extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_categoria';

    public function productos()
    {
        return $this->hasMany(ProductoSku::class, 'id_categoria');
    }
}
