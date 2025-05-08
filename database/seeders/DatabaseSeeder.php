<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    $this->call([
        CategoriaSeeder::class,
        ProductoSkuSeeder::class,
        UbicacionSeeder::class,
        ResponsableSeeder::class,
        ResguardanteSeeder::class,
        EstadoSeeder::class,
        InventarioSeeder::class,
        TransaccionSeeder::class,
        UserSeeder::class,
    ]);
}
}
