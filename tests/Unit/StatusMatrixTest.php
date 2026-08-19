<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use App\Domain\User\UserStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusMatrixTest extends TestCase
{
    /**
     * @param  class-string<\BackedEnum>  $enumClass
     * @param  list<string>  $values
     * @param  list<string>  $allowedTransitions
     */
    #[DataProvider('matrixProvider')]
    public function test_status_matrix_is_exhaustive(
        string $enumClass,
        array $values,
        array $allowedTransitions,
    ): void {
        $cases = $enumClass::cases();

        self::assertSame($values, array_map(static fn (\BackedEnum $case): int|string => $case->value, $cases));

        foreach ($cases as $from) {
            foreach ($cases as $to) {
                assert($from instanceof UserStatus || $from instanceof GroupStatus || $from instanceof PaymentStatus);
                assert($to instanceof UserStatus || $to instanceof GroupStatus || $to instanceof PaymentStatus);
                self::assertSame(
                    in_array("{$from->value}:{$to->value}", $allowedTransitions, true),
                    $from->canTransitionTo($to),
                    "Unexpected transition rule {$from->value} -> {$to->value}",
                );
            }
        }
    }

    /**
     * @return array<string, array{class-string<\BackedEnum>, list<string>, list<string>}>
     */
    public static function matrixProvider(): array
    {
        return [
            'user' => [UserStatus::class, ['pending', 'approved', 'rejected'], [
                'pending:approved',
                'pending:rejected',
                'rejected:pending',
            ]],
            'group' => [GroupStatus::class, [
                'awaiting_payment', 'draft', 'moderation', 'revision',
                'rejected', 'approved', 'active', 'expired',
            ], [
                'awaiting_payment:draft',
                'draft:moderation',
                'moderation:approved',
                'moderation:revision',
                'moderation:rejected',
                'revision:moderation',
                'approved:active',
                'active:expired',
                'expired:approved',
            ]],
            'payment' => [PaymentStatus::class, [
                'created', 'pending', 'succeeded', 'failed', 'cancelled', 'refunded',
            ], [
                'created:pending',
                'pending:succeeded',
                'pending:failed',
                'pending:cancelled',
                'succeeded:refunded',
            ]],
        ];
    }
}
