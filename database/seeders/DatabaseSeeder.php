<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // List of seeders to run
        $seeders = [
            InitialUserSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            if (class_exists($seeder)) {
                $this->call($seeder);
                // Log success message for each seeder
                Log::info("DatabaseSeeder: seeder {$seeder} executed successfully.");
                $this->command->info("DatabaseSeeder: seeder {$seeder} executed successfully.");
            } else {
                // Log error message if seeder class does not works
                Log::error("DatabaseSeeder: seeder {$seeder} does not works.");
                $this->command->error("DatabaseSeeder: seeder {$seeder} does not works.");
            }
        }
    }
}
