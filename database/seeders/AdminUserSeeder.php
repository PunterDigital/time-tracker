<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or find the 'admin' role
        $role = Role::firstOrCreate(['name' => 'admin']);

        // Create the user
        $user = User::create([
            'name' => 'Admin', // You might need to set a 'name' attribute
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin'),
        ]);

        // Create a personal team for the user
        $user->ownedTeams()->create([
            'name' => 'Admin\'s Team',
            'personal_team' => true,
        ]);

        // Assign the admin role to the user
        $user->assignRole('admin');
    }
}
