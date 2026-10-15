<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'admin@ngeblog.test',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Siti Rahayu',
            'email' => 'user@ngeblog.test',
            'role' => 'visitor',
        ]);

        $this->call([
            CategorySeeder::class,
            ArticleSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
