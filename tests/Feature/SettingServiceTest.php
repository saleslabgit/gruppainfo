<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingService;
use App\Domain\Settings\SettingType;
use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;
use UnexpectedValueException;

final class SettingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_integer_and_null_values_are_returned_with_their_types(): void
    {
        $this->seed(SettingSeeder::class);
        $service = $this->app->make(SettingService::class);

        self::assertSame(30, $service->integer(SettingKey::PlacementDurationDays));
        self::assertNull($service->integer(SettingKey::PlacementPriceMinor));
    }

    public function test_values_are_cached_until_explicit_invalidation(): void
    {
        $this->seed(SettingSeeder::class);
        $service = $this->app->make(SettingService::class);

        self::assertSame(30, $service->integer(SettingKey::PlacementDurationDays));
        Setting::query()->where('key', SettingKey::PlacementDurationDays)->update(['value' => '45']);
        self::assertSame(30, $service->integer(SettingKey::PlacementDurationDays));

        $service->forget(SettingKey::PlacementDurationDays);
        self::assertSame(45, $service->integer(SettingKey::PlacementDurationDays));

        $service->set(SettingKey::PlacementDurationDays, SettingType::Integer, 60);
        self::assertSame(60, $service->integer(SettingKey::PlacementDurationDays));
    }

    public function test_type_mismatches_and_invalid_values_fail_clearly(): void
    {
        $this->seed(SettingSeeder::class);
        $service = $this->app->make(SettingService::class);

        try {
            $service->string(SettingKey::PlacementDurationDays);
            self::fail('A setting type mismatch was accepted.');
        } catch (UnexpectedValueException $exception) {
            self::assertStringContainsString('expected [string]', $exception->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $service->set('invalid_integer', SettingType::Integer, '30');
    }
}
