<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use App\Models\Setting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use InvalidArgumentException;
use UnexpectedValueException;

final class SettingService
{
    public function __construct(private readonly CacheRepository $cache) {}

    public function integer(string $key): ?int
    {
        $value = $this->get($key, SettingType::Integer);

        return $value === null ? null : (int) $value;
    }

    public function string(string $key): ?string
    {
        $value = $this->get($key, SettingType::String);

        return $value === null ? null : (string) $value;
    }

    public function boolean(string $key): ?bool
    {
        $value = $this->get($key, SettingType::Boolean);

        return $value === null ? null : (bool) $value;
    }

    public function set(string $key, SettingType $type, int|string|bool|null $value): void
    {
        $this->assertValueMatchesType($type, $value);

        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['type' => $type, 'value' => $this->encode($value)],
        );

        $this->forget($key);
    }

    public function forget(string $key): void
    {
        $this->cache->forget($this->cacheKey($key));
    }

    private function get(string $key, SettingType $expectedType): int|string|bool|null
    {
        /** @var array{type: SettingType, value: int|string|bool|null} $cached */
        $cached = $this->cache->rememberForever($this->cacheKey($key), function () use ($key): array {
            $setting = Setting::query()->where('key', $key)->first();

            if ($setting === null) {
                throw new UnexpectedValueException("Unknown setting [{$key}].");
            }

            return [
                'type' => $setting->type,
                'value' => $this->decode($setting->type, $setting->value),
            ];
        });

        if ($cached['type'] !== $expectedType) {
            throw new UnexpectedValueException(
                "Setting [{$key}] has type [{$cached['type']->value}], expected [{$expectedType->value}].",
            );
        }

        return $cached['value'];
    }

    private function cacheKey(string $key): string
    {
        return "gp_setting:{$key}";
    }

    private function encode(int|string|bool|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private function decode(SettingType $type, ?string $value): int|string|bool|null
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            SettingType::Integer => (int) $value,
            SettingType::String => $value,
            SettingType::Boolean => match ($value) {
                '1' => true,
                '0' => false,
                default => throw new UnexpectedValueException("Invalid boolean setting value [{$value}]."),
            },
        };
    }

    private function assertValueMatchesType(SettingType $type, int|string|bool|null $value): void
    {
        $matches = $value === null || match ($type) {
            SettingType::Integer => is_int($value),
            SettingType::String => is_string($value),
            SettingType::Boolean => is_bool($value),
        };

        if (! $matches) {
            throw new InvalidArgumentException("Value does not match setting type [{$type->value}].");
        }
    }
}
