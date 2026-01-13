<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@example.com')->first();

        if ($user) {
            Admin::firstOrCreate(
                ['user_id' => $user->id],
                ['name' => $user->name]
            );
        }
    }
}
