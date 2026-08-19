<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserDocumentType: string
{
    case Diploma = 'diploma';
    case Certificate = 'certificate';
    case License = 'license';
    case StateRegistration = 'state_registration';

    public function label(): string
    {
        return match ($this) {
            self::Diploma => 'Диплом',
            self::Certificate => 'Сертификат',
            self::License => 'Лицензия или членство',
            self::StateRegistration => 'Свидетельство о государственной регистрации',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
