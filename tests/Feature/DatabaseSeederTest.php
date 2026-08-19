<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingType;
use App\Domain\User\UserStatus;
use App\Models\Dictionary;
use App\Models\DictionaryItem;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seed_is_complete_and_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        self::assertSame(
            ['education_type', 'gender', 'group_format'],
            Dictionary::query()->orderBy('code')->pluck('code')->all(),
        );
        self::assertSame(0, DictionaryItem::query()->count());
        self::assertSame(7, Setting::query()->count());

        $expectedSettings = [
            SettingKey::PlacementPriceMinor => null,
            SettingKey::ExtensionPriceMinor => null,
            SettingKey::PlacementDurationDays => '30',
            SettingKey::ExpiryWarningDays => '3',
            SettingKey::ExtensionWindowDays => '30',
            SettingKey::ApplicationRetentionMonths => '12',
            SettingKey::PasswordSetupLifetimeHours => '72',
        ];

        foreach ($expectedSettings as $key => $value) {
            $setting = Setting::query()->where('key', $key)->firstOrFail();
            self::assertSame(SettingType::Integer, $setting->type);
            self::assertSame($value, $setting->value);
        }

        self::assertSame(1, User::query()->where('admin', true)->count());
        $admin = User::query()->where('email', (string) config('seed.admin.email'))->firstOrFail();
        self::assertSame(UserStatus::Approved, $admin->status);
        self::assertTrue($admin->admin);
        self::assertFalse($admin->disabled);
        self::assertFalse($admin->free);
        self::assertFalse((bool) $admin->getRawOriginal('accept'));
        self::assertTrue(Hash::check((string) config('seed.admin.password'), (string) $admin->password));

        self::assertSame(1, User::query()->where('admin', false)->count());
        $psychologist = User::query()->where('email', (string) config('seed.psychologist.email'))->firstOrFail();
        self::assertSame(UserStatus::Approved, $psychologist->status);
        self::assertFalse($psychologist->admin);
        self::assertFalse($psychologist->disabled);
        self::assertTrue(Hash::check((string) config('seed.psychologist.password'), (string) $psychologist->password));
    }

    public function test_production_seed_does_not_create_development_accounts(): void
    {
        $this->app->instance('env', 'production');

        try {
            $this->artisan('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ])->assertSuccessful();

            self::assertSame(0, User::query()->count());
        } finally {
            $this->app->instance('env', 'testing');
        }
    }
}
