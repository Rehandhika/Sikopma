<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder uses the centralized configuration from config/roles.php
     * to ensure consistency across the application.
     *
     * @see config/roles.php
     */
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get configuration
        $permissions = config('roles.permissions');
        $rolePermissions = config('roles.role_permissions');
        $systemRoles = config('roles.system_roles');
        $orgRoles = config('roles.organization_roles', []);

        // Create all permissions from config
        foreach ($permissions as $permName => $permConfig) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }

        // Create system roles if they don't exist
        foreach ($systemRoles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        // Create organization roles if they don't exist
        foreach (array_keys($orgRoles) as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        // Clear cache again after creating roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Assign permissions to system roles based on config
        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();

            if ($role) {
                if ($roleName === config('roles.super_admin_role')) {
                    $allPermissions = array_keys($permissions);
                    $role->syncPermissions($allPermissions);
                } else {
                    $role->syncPermissions($perms);
                }
            }
        }

        // Assign permissions to organization roles based on mapping
        foreach ($orgRoles as $roleName => $config) {
            $mappedTo = $config['maps_to'] ?? null;
            if ($mappedTo && isset($rolePermissions[$mappedTo])) {
                $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
                if ($role) {
                    $role->syncPermissions($rolePermissions[$mappedTo]);
                }
            }
        }

        // Clear cache one final time
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('Permissions and roles seeded successfully from config/roles.php');
        $this->command->info('- Created '.count($permissions).' permissions');
        $this->command->info('- Created '.(count($systemRoles) + count($orgRoles)).' roles');
    }
}
