<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Profile;

class ProfilesTableSeeder extends Seeder
{
    public function run(): void
    {
        // 既存ユーザー全員に紐づける
        $users = User::all();

        $users->each(function ($user) {
            Profile::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
