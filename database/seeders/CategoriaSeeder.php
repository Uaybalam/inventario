<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;


class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Categoria::create(['nombre' => 'Electrónica', 'descripcion' => 'Dispositivos electrónicos']);
        Categoria::create(['nombre' => 'Papelería', 'descripcion' => 'Material de oficina']);
        Categoria::create(['nombre' => 'Mobiliario', 'descripcion' => 'Mesas, sillas, etc.']);
    }
}
