<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Renan Rodrigues dos Santos',
            'email' => 'renan@renanrodrigues.cloud',
            'password' => bcrypt('12345678')
        ]);

        User::create([
            'name' => 'Raiara Rodrigues dos Santos',
            'email' => 'raiara@renanrodrigues.cloud',
            'password' => bcrypt('12345678')
        ]);

        User::create([
            'name' => 'Maristânea Pereira de Souza',
            'email' => 'maristanea@renanrodrigues.cloud',
            'password' => bcrypt('12345678')
        ]);

        User::create([
            'name' => 'Rariele Rodrigues dos Reis Batista',
            'email' => 'rariele@renanrodrigues.cloud',
            'password' => bcrypt('12345678')
        ]);

        User::create([
            'name' => 'Fernando Mendes Lima',
            'email' => 'fernando@renanrodrigues.cloud',
            'password' => bcrypt('12345678')
        ]);
    }
}
