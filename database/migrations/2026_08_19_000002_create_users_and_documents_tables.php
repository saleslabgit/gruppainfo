<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gp_users', function (Blueprint $table): void {
            $table->id();
            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email');
            $table->foreignId('education_type_id')->nullable()->constrained('gp_dictionary_items')->restrictOnDelete();
            $table->string('other_education')->nullable();
            $table->string('modality')->nullable();
            $table->string('training_center')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->unsignedInteger('training_hours')->nullable();
            $table->string('license_number')->nullable();
            $table->date('license_expires_at')->nullable();
            $table->text('group_leading_experience')->nullable();
            $table->unsignedInteger('groups_held_count')->nullable();
            $table->boolean('documents_truth_confirmed')->nullable();
            $table->boolean('education_compliance_confirmed')->nullable();
            $table->boolean('ready_to_host_webinar')->nullable();
            $table->timestamp('personal_data_consent_at')->nullable();
            $table->string('personal_data_consent_version')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('accept')->default(false);
            $table->boolean('disabled')->default(false)->index();
            $table->boolean('free')->default(false)->index();
            $table->boolean('admin')->default(false);
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->string('active_email')->storedAs('case when `deleted_at` is null then `email` else null end');

            $table->unique('active_email');
        });

        Schema::create('gp_user_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('gp_users')->restrictOnDelete();
            $table->string('type');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gp_user_documents');
        Schema::dropIfExists('gp_users');
    }
};
