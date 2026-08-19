<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DevelopmentPsychologistSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => (string) config('seed.psychologist.email')],
            [
                'first_name' => (string) config('seed.psychologist.first_name'),
                'password' => Hash::make((string) config('seed.psychologist.password')),
                'status' => UserStatus::Approved,
                'admin' => false,
                'disabled' => false,
                'free' => false,
            ],
        );
    }
}
