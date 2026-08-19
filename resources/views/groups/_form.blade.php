@php
    $group = $group ?? null;
    $value = static fn (string $field, mixed $fallback = '') => old($field, data_get($group, $field, $fallback));
    $price = old('price_per_meeting', App\Support\MoneyParser::fromMinorUnits($group?->price_per_meeting));
@endphp
<x-ui.form-field label="Название" name="name" :error="$errors->first('name')"><x-ui.input name="name" :value="$value('name')" :error="$errors->has('name')" /></x-ui.form-field>
<x-ui.form-field label="Описание" name="description" :error="$errors->first('description')"><x-ui.textarea name="description" :error="$errors->has('description')" rows="6">{{ $value('description') }}</x-ui.textarea></x-ui.form-field>
<x-ui.form-field label="Расписание" name="schedule" :error="$errors->first('schedule')"><x-ui.textarea name="schedule" :error="$errors->has('schedule')" rows="3">{{ $value('schedule') }}</x-ui.textarea></x-ui.form-field>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Формат" name="format_id" :error="$errors->first('format_id')"><x-ui.select name="format_id" :value="(string) $value('format_id')" :options="['' => 'Не указано'] + $formats" :error="$errors->has('format_id')" /></x-ui.form-field>
    <x-ui.form-field label="Пол участников" name="gender_id" :error="$errors->first('gender_id')"><x-ui.select name="gender_id" :value="(string) $value('gender_id')" :options="['' => 'Не указано'] + $genders" :error="$errors->has('gender_id')" /></x-ui.form-field>
</div>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Длительность встречи, минут" name="meeting_duration_minutes" :error="$errors->first('meeting_duration_minutes')"><x-ui.input name="meeting_duration_minutes" type="number" min="1" :value="$value('meeting_duration_minutes')" :error="$errors->has('meeting_duration_minutes')" /></x-ui.form-field>
    <x-ui.form-field label="Количество участников" name="participant_count" :error="$errors->first('participant_count')"><x-ui.input name="participant_count" type="number" min="1" :value="$value('participant_count')" :error="$errors->has('participant_count')" /></x-ui.form-field>
</div>
<x-ui.form-field label="Стоимость встречи" name="price_per_meeting" helper="Введите сумму в BYN, не более двух знаков после запятой." :error="$errors->first('price_per_meeting')"><x-ui.money-input name="price_per_meeting" :value="$price" :error="$errors->has('price_per_meeting')" /></x-ui.form-field>
