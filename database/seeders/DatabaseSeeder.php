<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        User::factory()->teacher()->create([
            'email' => 'teacher@example.com',
        ]);
        User::factory()->teacher()->count(2)->create();

        User::factory()->create([
            'email' => 'student@example.com',
        ]);
        User::factory()->count(29)->create();
    }
}
