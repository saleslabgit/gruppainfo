<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gp_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('owner_id')->constrained('gp_users')->restrictOnDelete();
            $table->foreignId('group_id')->constrained('gp_groups')->restrictOnDelete();
            $table->string('type')->index();
            $table->string('transaction_id')->nullable()->unique();
            $table->unsignedBigInteger('amount');
            $table->char('currency', 3)->default('BYN');
            $table->string('status')->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_comment')->nullable();
            $table->json('bank_response')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('group_id');
        });

        Schema::create('gp_payment_webhooks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained('gp_payments')->nullOnDelete();
            $table->string('transaction_id')->nullable();
            $table->longText('payload');
            $table->boolean('signature_valid');
            $table->boolean('processed')->default(false);
            $table->string('result')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gp_payment_webhooks');
        Schema::dropIfExists('gp_payments');
    }
};
