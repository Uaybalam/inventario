<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleados extends Model
{
    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
    ];
}
