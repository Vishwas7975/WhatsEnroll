<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Credentials MUST come from environment -- never commit real values.
        // Set ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD in your .env before running the seeder.
        $email    = env('ADMIN_SEED_EMAIL');
        $password = env('ADMIN_SEED_PASSWORD');

        if (!$email || !$password) {
            $this->command->error(
                'ADMIN_SEED_EMAIL and ADMIN_SEED_PASSWORD must be set in your .env before seeding.'
            );
            return;
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'support']);

        // Create super admin user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($password),
            ]
        );

        // Assign super admin role
        $user->assignRole($superAdmin);

        $this->command->info("Admin user created/updated: {$email}");
    }
}
