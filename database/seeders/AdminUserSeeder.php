<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@patf.com'],
            [
                'name' => 'PATF Admin',
                'password' => Hash::make('password'),
                'role' => 'super',
                'status' => 'active',
                'company_name' => 'PATF Admin',
                'assessments_allowed' => -1,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ Admin user ready — admin@patf.com / password');
    }
}
