<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'             => 'Administrador',
            'email'            => 'admin@asem.com',
            'password'         => Hash::make('admin1234'),
            'role'             => 'admin',
            'numero_documento' => '1000000001',
        ]);

        // Coordinacion
        User::create([
            'name'             => 'Coordinador Principal',
            'email'            => 'coordinacion@asem.com',
            'password'         => Hash::make('coord1234'),
            'role'             => 'coordinacion',
            'numero_documento' => '1000000002',
        ]);

        // Instructor de prueba
        User::create([
            'name'             => 'Instructor Demo',
            'email'            => 'instructor@asem.com',
            'password'         => Hash::make('inst1234'),
            'role'             => 'instructor',
            'numero_documento' => '1000000003',
        ]);

        // Aprendiz de prueba
        User::create([
            'name'             => 'Aprendiz Demo',
            'email'            => 'aprendiz@asem.com',
            'password'         => Hash::make('apr1234'),
            'role'             => 'aprendiz',
            'numero_documento' => '1000000004',
        ]);
    }
}
