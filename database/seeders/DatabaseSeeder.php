<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events stay on, so the case workflow runs for demo data.
     */
    public function run(): void
    {
        $this->call(TaskTemplateSeeder::class);

        if (! app()->isLocal()) {
            $this->command?->info('Ersten Admin anlegen mit: php artisan backoffice:create-admin');

            return;
        }

        // Local development sign-in only. Never use these outside your machine.
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@rivocare.test',
            'password' => 'local-dev-password',
            'role' => UserRole::Admin,
        ]);

        User::query()->create([
            'name' => 'Team Member',
            'email' => 'team@rivocare.test',
            'password' => 'local-dev-password',
            'role' => UserRole::Team,
        ]);

        $this->call(DemoSeeder::class);
    }
}
