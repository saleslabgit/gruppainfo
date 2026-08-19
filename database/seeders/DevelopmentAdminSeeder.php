<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DevelopmentAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => (string) config('seed.admin.email')],
            [
                'first_name' => (string) config('seed.admin.first_name'),
                'password' => Hash::make((string) config('seed.admin.password')),
                'status' => UserStatus::Approved,
                'admin' => true,
                'disabled' => false,
                'free' => false,
            ],
        );
    }
}
