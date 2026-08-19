<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Group\GroupStatus;
use App\Domain\Payment\PaymentStatus;
use App\Domain\User\UserStatus;
use App\Models\Dictionary;
use App\Models\DictionaryItem;
use App\Models\Group;
use App\Models\GroupApplication;
use App\Models\GroupStatusHistory;
use App\Models\Payment;
use App\Models\PaymentWebhook;
use App\Models\User;
use App\Models\UserDocument;
use App\Support\MoneyFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DomainModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_use_expected_tables_and_relationships(): void
    {
        $dictionary = Dictionary::query()->create(['code' => 'group_format', 'name' => 'Формат']);
        $format = DictionaryItem::query()->create([
            'dictionary_id' => $dictionary->getKey(),
            'code' => 'online',
            'name' => 'Онлайн',
        ]);
        $user = $this->createUser('owner@example.test');
        $document = UserDocument::query()->create([
            'user_id' => $user->getKey(),
            'type' => 'diploma',
            'path' => 'private/example.pdf',
            'original_name' => 'example.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1234,
        ]);
        $group = Group::query()->create([
            'owner_id' => $user->getKey(),
            'status' => GroupStatus::Draft,
            'format_id' => $format->getKey(),
            'gender_id' => $format->getKey(),
            'name' => 'Test group',
        ]);
        $application = GroupApplication::query()->create([
            'group_id' => $group->getKey(),
            'last_name' => 'Иванов',
            'first_name' => 'Иван',
            'phone' => '+375 29 000-00-00',
            'phone_normalized' => '+375290000000',
        ]);
        $payment = Payment::query()->create([
            'owner_id' => $user->getKey(),
            'group_id' => $group->getKey(),
            'type' => 'placement',
            'amount' => 12345,
            'status' => PaymentStatus::Created,
        ]);
        $webhook = PaymentWebhook::query()->create([
            'payment_id' => $payment->getKey(),
            'payload' => '{}',
            'signature_valid' => true,
            'processed' => false,
        ]);
        $history = GroupStatusHistory::query()->create([
            'group_id' => $group->getKey(),
            'from_status' => null,
            'to_status' => GroupStatus::Draft,
            'actor_id' => $user->getKey(),
            'actor_type' => 'user',
        ]);

        self::assertInstanceOf(HasMany::class, $user->documents());
        self::assertInstanceOf(HasMany::class, $user->groups());
        self::assertInstanceOf(HasMany::class, $user->payments());
        self::assertInstanceOf(BelongsTo::class, $group->owner());
        self::assertInstanceOf(HasMany::class, $group->applications());
        self::assertInstanceOf(HasMany::class, $group->payments());
        self::assertInstanceOf(HasMany::class, $group->statusHistory());
        self::assertInstanceOf(BelongsTo::class, $group->format());
        self::assertInstanceOf(BelongsTo::class, $group->gender());
        self::assertTrue($user->documents->contains($document));
        self::assertTrue($user->groups->contains($group));
        self::assertTrue($group->applications->contains($application));
        self::assertTrue($group->payments->contains($payment));
        self::assertTrue($group->statusHistory->contains($history));
        self::assertTrue($payment->webhooks->contains($webhook));
        self::assertTrue($dictionary->items->contains($format));
        self::assertTrue($format->dictionary->is($dictionary));
    }

    public function test_group_uuid_is_generated_uniquely_by_model(): void
    {
        $user = $this->createUser('uuid-owner@example.test');
        $first = Group::query()->create(['owner_id' => $user->getKey(), 'status' => GroupStatus::Draft]);
        $second = Group::query()->create(['owner_id' => $user->getKey(), 'status' => GroupStatus::Draft]);

        self::assertNotEmpty($first->public_uuid);
        self::assertNotSame($first->public_uuid, $second->public_uuid);
    }

    public function test_active_email_is_unique_and_can_be_reused_after_soft_delete(): void
    {
        $first = $this->createUser('unique@example.test');

        try {
            $this->createUser('unique@example.test');
            self::fail('A second active email was accepted.');
        } catch (QueryException) {
            self::assertSame(1, User::query()->where('email', 'unique@example.test')->count());
        }

        $first->delete();
        $replacement = $this->createUser('unique@example.test');

        self::assertNotSame($first->getKey(), $replacement->getKey());
        self::assertSame(1, User::query()->where('email', 'unique@example.test')->count());
        self::assertSame(2, User::withTrashed()->where('email', 'unique@example.test')->count());
    }

    public function test_money_is_stored_exactly_in_minor_units_and_formatted(): void
    {
        $user = $this->createUser('money-owner@example.test');
        $group = Group::query()->create([
            'owner_id' => $user->getKey(),
            'status' => GroupStatus::Draft,
            'price_per_meeting' => 900719925474099,
        ]);
        $payment = Payment::query()->create([
            'owner_id' => $user->getKey(),
            'group_id' => $group->getKey(),
            'type' => 'placement',
            'amount' => 900719925474099,
            'status' => PaymentStatus::Created,
        ]);

        self::assertSame(900719925474099, $group->fresh()->price_per_meeting);
        self::assertSame(900719925474099, $payment->fresh()->amount);
        self::assertSame('9 007 199 254 740,99 BYN', MoneyFormatter::format($payment->fresh()->amount));
    }

    private function createUser(string $email): User
    {
        return User::query()->create([
            'email' => $email,
            'status' => UserStatus::Approved,
            'password' => 'test-password',
        ]);
    }
}
