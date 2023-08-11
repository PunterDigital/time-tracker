<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for projects
        Permission::create(['name' => 'view projects']);
        Permission::create(['name' => 'create projects']);
        Permission::create(['name' => 'edit projects']);
        Permission::create(['name' => 'delete projects']);
        Permission::create(['name' => 'add users to projects']);

        // Create permissions for time tracking
        Permission::create(['name' => 'start time tracking']);
        Permission::create(['name' => 'stop time tracking']);
        Permission::create(['name' => 'view time entries']);
        Permission::create(['name' => 'view time entries others']);
        Permission::create(['name' => 'edit time entries']);
        Permission::create(['name' => 'delete time entries']);

        // Create permissions for reports
        Permission::create(['name' => 'view reports']);

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'admin']);
        $manager = Role::create(['name' => 'employee']);

        // Admin has all permissions
        $admin->givePermissionTo(Permission::all());

        // Manager can view and edit projects, view and manage time tracking
        $manager->givePermissionTo('view projects', 'view time entries', 'start time tracking', 'stop time tracking');
    }
}
