<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;


class RolesPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'nombre' => 'Administrador', 'descripcion' => 'Gestion total de la aplicacion'],
            ['id' => 2, 'nombre' => 'Direccion', 'descripcion' => 'Firma convenios por parte del centro'],
            ['id' => 3, 'nombre' => 'Coordinador FFE', 'descripcion' => 'Gestion de convenios por departamento'],
            ['id' => 4, 'nombre' => 'Profesor tutor', 'descripcion' => 'Gestion y validacion de convenios asignados'],
            ['id' => 5, 'nombre' => 'Profesor', 'descripcion' => 'Consulta de convenios sin modificacion'],
            ['id' => 6, 'nombre' => 'Secretaria', 'descripcion' => 'Generacion y carga documental de convenios'],
            ['id' => 7, 'nombre' => 'Empresa externa', 'descripcion' => 'Acceso limitado a sus propios convenios y datos'],
        ];

        foreach ($roles as $role) {
            Rol::updateOrCreate(
                ['id' => $role['id']],
                ['nombre' => $role['nombre'], 'descripcion' => $role['descripcion']]
            );
        }
    }
}
