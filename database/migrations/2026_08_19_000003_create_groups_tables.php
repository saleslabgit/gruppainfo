<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gp_groups', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_uuid')->unique();
            $table->foreignId('owner_id')->constrained('gp_users')->restrictOnDelete();
            $table->string('status')->index();
            $table->boolean('disabled')->default(false);
            $table->boolean('accept')->default(false);
            $table->boolean('free')->default(false);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->text('schedule')->nullable();
            $table->foreignId('format_id')->nullable()->constrained('gp_dictionary_items')->restrictOnDelete();
            $table->unsignedInteger('meeting_duration_minutes')->nullable();
            $table->unsignedInteger('participant_count')->nullable();
            $table->foreignId('gender_id')->nullable()->constrained('gp_dictionary_items')->restrictOnDelete();
            $table->unsignedBigInteger('price_per_meeting')->nullable();
            $table->text('moderator_comment')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('external_catalog_id')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('expiry_warning_sent_at')->nullable();
            $table->unsignedInteger('placement_days')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index(['status', 'expires_at']);
            $table->index(['owner_id', 'status']);
        });

        Schema::create('gp_group_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('gp_groups')->restrictOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('actor_id')->nullable()->constrained('gp_users')->nullOnDelete();
            $table->string('actor_type');
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('group_id');
        });

        Schema::create('gp_group_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('gp_groups')->restrictOnDelete();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('phone');
            $table->string('phone_normalized');
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();

            $table->index('group_id');
            $table->index(['group_id', 'processed_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gp_group_applications');
        Schema::dropIfExists('gp_group_status_history');
        Schema::dropIfExists('gp_groups');
    }
};
