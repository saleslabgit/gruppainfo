<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Dictionary;
use App\Models\Group;
use App\Support\MoneyParser;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use InvalidArgumentException;

abstract class GroupDataRequest extends FormRequest
{
    abstract protected function complete(): bool;

    public function rules(): array
    {
        $presence = $this->complete() ? ['required'] : ['nullable'];

        return [
            'name' => [...$presence, 'string', 'max:255'],
            'description' => [...$presence, 'string'],
            'schedule' => [...$presence, 'string'],
            'format_id' => [...$presence, 'integer', $this->activeDictionaryItem('group_format')],
            'meeting_duration_minutes' => [...$presence, 'integer', 'min:1', 'max:4294967295'],
            'participant_count' => [...$presence, 'integer', 'min:1', 'max:4294967295'],
            'gender_id' => [...$presence, 'integer', $this->activeDictionaryItem('gender')],
            'price_per_meeting' => [...$presence, 'string', function (string $attribute, mixed $value, Closure $fail): void {
                try {
                    MoneyParser::toMinorUnits((string) $value);
                } catch (InvalidArgumentException $exception) {
                    $fail($exception->getMessage());
                }
            }],
        ];
    }

    /** @return array<string, mixed> */
    public function groupData(): array
    {
        $data = $this->safe()->only([
            'name', 'description', 'schedule', 'format_id', 'meeting_duration_minutes',
            'participant_count', 'gender_id', 'price_per_meeting',
        ]);

        if (array_key_exists('price_per_meeting', $data) && $data['price_per_meeting'] !== null) {
            $data['price_per_meeting'] = MoneyParser::toMinorUnits((string) $data['price_per_meeting']);
        }

        return $data;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите название группы.',
            'description.required' => 'Добавьте описание группы.',
            'schedule.required' => 'Укажите расписание.',
            'format_id.required' => 'Выберите формат группы.',
            'meeting_duration_minutes.required' => 'Укажите длительность встречи.',
            'participant_count.required' => 'Укажите количество участников.',
            'gender_id.required' => 'Выберите пол участников.',
            'price_per_meeting.required' => 'Укажите стоимость встречи.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $normalized = [];
        foreach (['name', 'description', 'schedule', 'format_id', 'meeting_duration_minutes', 'participant_count', 'gender_id', 'price_per_meeting'] as $field) {
            if ($this->exists($field)) {
                $value = $this->input($field);
                $normalized[$field] = is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value;
            }
        }
        $this->merge($normalized);
    }

    private function activeDictionaryItem(string $code): Exists
    {
        return Rule::exists('gp_dictionary_items', 'id')->where(function ($query) use ($code): void {
            $query->where('active', true)->whereIn('dictionary_id', Dictionary::query()
                ->select('id')->where('code', $code)->where('active', true));
        });
    }

    protected function routeGroup(): ?Group
    {
        $group = $this->route('group');

        return $group instanceof Group ? $group : null;
    }
}
