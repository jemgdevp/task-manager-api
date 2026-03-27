<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class InitialUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fullName = 'Admin';
        $email = 'admin@taskmanager.jemg.dev';
        $password = 'password';

        try{
            User::updateOrCreate([
                'email' => $email,
            ], [
                'name' => $fullName,
                'password' => Hash::make($password),
            ]);
        } catch (\Exception $e) {
            $this->command->error('Failed to create initial user: ' . $e->getMessage());
            Log::error('Failed to create initial user: ' . $e->getMessage());
        }
    }

}
