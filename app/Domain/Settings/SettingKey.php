<?php

declare(strict_types=1);

namespace App\Domain\Settings;

final class SettingKey
{
    public const PlacementPriceMinor = 'placement_price_minor';

    public const ExtensionPriceMinor = 'extension_price_minor';

    public const PlacementDurationDays = 'placement_duration_days';

    public const ExpiryWarningDays = 'expiry_warning_days';

    public const ExtensionWindowDays = 'extension_window_days';

    public const ApplicationRetentionMonths = 'application_retention_months';

    public const PasswordSetupLifetimeHours = 'password_setup_lifetime_hours';

    private function __construct() {}
}
