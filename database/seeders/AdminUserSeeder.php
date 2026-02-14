<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('email', 'admin@patf.com')->update([
            'role' => 'super',
            'status' => 'active',
            'company_name' => 'PATF Admin',
            'assessments_allowed' => -1,
        ]);

        $this->command->info('✅ Admin user updated with super role');
    }
}
