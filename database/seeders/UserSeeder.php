<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
{
    Role::firstOrCreate(['name' => 'Administrador']);
    Role::firstOrCreate(['name' => 'Responsable']);
    Role::firstOrCreate(['name' => 'Usuario']);

    $admin = User::create([
        'nombre' => 'Admin',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'rol' => 'Administrador',
        'estado' => 'Activo',
        'fecha_creacion' => now(),
    ]);

    $admin->assignRole('Administrador');
}
}
