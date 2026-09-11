<?php

namespace Database\Seeders;

use App\n2_System\Access\Models\Role;
use App\n2_System\Identity\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->roles()->attach(Role::query()->where('code', Role::SUPER_ADMIN)->sole());
    }
}
