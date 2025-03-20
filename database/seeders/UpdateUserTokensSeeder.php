<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UpdateUserTokensSeeder extends Seeder
{
    public function run()
    {
        User::whereNull('api_token')->each(function ($user) {
            $user->update([
                'api_token' => Str::random(80)
            ]);
        });
    }
} 