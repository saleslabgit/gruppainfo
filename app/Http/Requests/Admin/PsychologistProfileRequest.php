<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Dictionary;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class PsychologistProfileRequest extends FormRequest
{
    /** @var list<string> */
    private const PROFILE_FIELDS = [
        'last_name',
        'first_name',
        'middle_name',
        'phone',
        'email',
        'education_type_id',
        'other_education',
        'modality',
        'training_center',
        'graduation_year',
        'training_hours',
        'license_number',
        'license_expires_at',
        'group_leading_experience',
        'groups_held_count',
        'documents_truth_confirmed',
        'education_compliance_confirmed',
        'ready_to_host_webinar',
        'personal_data_consent_at',
        'personal_data_consent_version',
    ];

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }

    /** @return array<string, mixed> */
    protected function profileRules(?User $ignoredUser = null): array
    {
        $educationDictionaryId = Dictionary::query()
            ->where('code', 'education_type')
            ->where('active', true)
            ->value('id');
        $emailRule = Rule::unique('gp_users', 'active_email');

        if ($ignoredUser !== null) {
            $emailRule->ignore($ignoredUser->getKey());
        }

        return [
            'last_name' => ['nullable', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'education_type_id' => [
                'nullable',
                Rule::exists('gp_dictionary_items', 'id')->where(fn ($query) => $query
                    ->where('dictionary_id', $educationDictionaryId ?? 0)
                    ->where('active', true)),
            ],
            'other_education' => ['nullable', 'string', 'max:255'],
            'modality' => ['nullable', 'string', 'max:255'],
            'training_center' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'min:1900', 'max:'.now()->year],
            'training_hours' => ['nullable', 'integer', 'min:0'],
            'license_number' => ['nullable', 'string', 'max:255'],
            'license_expires_at' => ['nullable', 'date'],
            'group_leading_experience' => ['nullable', 'string'],
            'groups_held_count' => ['nullable', 'integer', 'min:0'],
            'documents_truth_confirmed' => ['nullable', 'boolean'],
            'education_compliance_confirmed' => ['nullable', 'boolean'],
            'ready_to_host_webinar' => ['nullable', 'boolean'],
            'personal_data_consent_at' => ['nullable', 'date'],
            'personal_data_consent_version' => ['nullable', 'string', 'max:255'],
        ];
    }

    /** @return array<string, mixed> */
    public function profileData(): array
    {
        $profile = $this->safe()->only(self::PROFILE_FIELDS);

        if (is_string($profile['personal_data_consent_at'] ?? null)) {
            $profile['personal_data_consent_at'] = CarbonImmutable::parse(
                $profile['personal_data_consent_at'],
                (string) config('app.display_timezone'),
            )->utc();
        }

        return $profile;
    }
}
