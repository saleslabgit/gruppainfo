<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingType;
use App\Models\Setting;
use Illuminate\Database\Seeder;

final class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            SettingKey::PlacementPriceMinor => null,
            SettingKey::ExtensionPriceMinor => null,
            SettingKey::PlacementDurationDays => 30,
            SettingKey::ExpiryWarningDays => 3,
            SettingKey::ExtensionWindowDays => 30,
            SettingKey::ApplicationRetentionMonths => 12,
            SettingKey::PasswordSetupLifetimeHours => 72,
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'type' => SettingType::Integer,
                    'value' => $value === null ? null : (string) $value,
                ],
            );
        }
    }
}
