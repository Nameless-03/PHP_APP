<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Admin;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database with an admin user.
     */
    public function run(): void
    {
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'nombre' => 'Administrador',
                'password' => Hash::make('admin12345'),
                'role' => RoleEnum::ADMIN,
                'activo' => true,
            ]
        );

        if (!$admin->fecha_registro) {
            $admin->update(['fecha_registro' => now()]);
        }

        Admin::firstOrCreate(
            ['id_usuario' => $admin->id]
        );

        $this->command->info('✅ Usuario administrador creado: admin@example.com / admin12345');
    }
}
