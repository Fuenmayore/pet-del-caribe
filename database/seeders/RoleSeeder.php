<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        // Creamos los roles base para Pet del Caribe
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Operario']);
        Role::create(['name' => 'Supervisor']);
        Role::create(['name' => 'Calidad']);
        Role::create(['name' => 'Reproceso']);
    }
}
