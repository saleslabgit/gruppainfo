<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage_two_tables_and_core_columns_exist(): void
    {
        $tables = [
            'gp_users', 'gp_user_documents', 'gp_groups', 'gp_group_status_history',
            'gp_payments', 'gp_payment_webhooks', 'gp_group_applications',
            'gp_dictionaries', 'gp_dictionary_items', 'gp_settings',
            'sessions', 'jobs', 'failed_jobs',
        ];

        foreach ($tables as $table) {
            self::assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }

        self::assertTrue(Schema::hasColumns('gp_users', [
            'last_name', 'first_name', 'middle_name', 'phone', 'email', 'education_type_id',
            'other_education', 'modality', 'training_center', 'graduation_year', 'training_hours',
            'license_number', 'license_expires_at', 'group_leading_experience', 'groups_held_count',
            'documents_truth_confirmed', 'education_compliance_confirmed', 'ready_to_host_webinar',
            'personal_data_consent_at', 'personal_data_consent_version', 'status', 'accept',
            'disabled', 'free', 'admin', 'password', 'remember_token', 'deleted_at', 'active_email',
        ]));

        self::assertTrue(Schema::hasColumns('gp_groups', [
            'public_uuid', 'owner_id', 'status', 'disabled', 'accept', 'free', 'name',
            'description', 'schedule', 'format_id', 'meeting_duration_minutes', 'participant_count',
            'gender_id', 'price_per_meeting', 'moderator_comment', 'rejection_reason',
            'external_catalog_id', 'published_at', 'expires_at', 'expiry_warning_sent_at',
            'placement_days', 'deleted_at',
        ]));
    }

    public function test_required_indexes_and_generated_active_email_are_present(): void
    {
        $expectedIndexes = [
            'gp_users' => [
                ['active_email'], ['status'], ['disabled'], ['free'],
            ],
            'gp_groups' => [
                ['public_uuid'], ['owner_id'], ['status'], ['expires_at'],
                ['status', 'expires_at'], ['owner_id', 'status'],
            ],
            'gp_payments' => [
                ['transaction_id'], ['owner_id'], ['group_id'], ['status'], ['type'],
            ],
            'gp_group_applications' => [
                ['group_id'], ['processed_at'], ['group_id', 'processed_at'], ['created_at'],
            ],
            'gp_group_status_history' => [['group_id']],
            'gp_dictionary_items' => [['dictionary_id', 'code']],
        ];

        foreach ($expectedIndexes as $table => $expectedColumns) {
            $indexes = Schema::getIndexes($table);
            $actualColumns = array_map(
                static fn (array $index): array => $index['columns'],
                $indexes,
            );

            foreach ($expectedColumns as $columns) {
                self::assertContains($columns, $actualColumns, "Missing index on {$table} (".implode(', ', $columns).')');
            }
        }

        $userIndexes = Schema::getIndexes('gp_users');
        $userIndexColumns = array_map(static fn (array $index): array => $index['columns'], $userIndexes);
        self::assertNotContains(['email', 'deleted_at'], $userIndexColumns);
        self::assertNotContains(['email'], $userIndexColumns);

        $column = array_change_key_case((array) DB::selectOne(
            'select extra, generation_expression from information_schema.columns where table_schema = database() and table_name = ? and column_name = ?',
            ['gp_users', 'active_email'],
        ));

        self::assertStringContainsString('STORED GENERATED', (string) $column['extra']);
        self::assertStringContainsString('deleted_at', (string) $column['generation_expression']);
        self::assertStringContainsString('email', (string) $column['generation_expression']);
    }
}
