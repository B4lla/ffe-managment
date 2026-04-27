<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolesPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'usuarios.gestionar',      // Admin
            'convenios.generar',       // Secretaría
            'convenios.firmar',        // Dirección
            'convenios.validar',       // Coordinador / Tutor
            'convenios.ver-todos',     // Admin / Dirección
            'convenios.ver-depto',     // Coordinador
            'convenios.ver-propios',   // Tutor / Empresa
            'convenios.consultar',     // Profesor
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 2. CREAR ROLES Y ASIGNAR PERMISOS
        
        // Administrador
        Role::create(['name' => 'Administrador'])->givePermissionTo(Permission::all());

        // Dirección
        Role::create(['name' => 'Direccion'])->givePermissionTo([
            'convenios.firmar', 
            'convenios.ver-todos',
            'convenios.consultar'
        ]);

        // Secretaría
        Role::create(['name' => 'Secretaria'])->givePermissionTo([
            'convenios.generar', 
            'convenios.ver-todos',
            'convenios.consultar'
        ]);

        // Coordinador FFE
        Role::create(['name' => 'Coordinador'])->givePermissionTo([
            'convenios.validar', 
            'convenios.ver-depto',
            'convenios.consultar'
        ]);

        // Profesor Tutor
        Role::create(['name' => 'Tutor'])->givePermissionTo([
            'convenios.validar', 
            'convenios.ver-propios',
            'convenios.consultar'
        ]);

        // Profesor
        Role::create(['name' => 'Profesor'])->givePermissionTo([
            'convenios.consultar'
        ]);

        // Empresa Externa
        Role::create(['name' => 'Empresa'])->givePermissionTo([
            'convenios.ver-propios'
        ]);
    }
}
