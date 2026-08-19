<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\MoneyParser;

final class SubmitGroupRequest extends GroupDataRequest
{
    public function authorize(): bool
    {
        $group = $this->routeGroup();

        return $group !== null && ($this->user()?->can('submit', $group) ?? false);
    }

    protected function complete(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $group = $this->routeGroup();
        if ($group === null) {
            return;
        }

        $this->merge(array_filter([
            'name' => $this->input('name', $group->name),
            'description' => $this->input('description', $group->description),
            'schedule' => $this->input('schedule', $group->schedule),
            'format_id' => $this->input('format_id', $group->format_id),
            'meeting_duration_minutes' => $this->input('meeting_duration_minutes', $group->meeting_duration_minutes),
            'participant_count' => $this->input('participant_count', $group->participant_count),
            'gender_id' => $this->input('gender_id', $group->gender_id),
            'price_per_meeting' => $this->input('price_per_meeting', MoneyParser::fromMinorUnits($group->price_per_meeting)),
        ], static fn (mixed $value): bool => $value !== null && $value !== ''));
    }
}
