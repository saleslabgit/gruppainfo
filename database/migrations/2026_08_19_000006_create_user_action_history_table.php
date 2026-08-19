<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gp_user_action_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('target_user_id')->constrained('gp_users')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('gp_users')->nullOnDelete();
            $table->string('actor_type');
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('target_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gp_user_action_history');
    }
};
