@php
    $psychologist = $psychologist ?? null;
    $booleanOptions = ['' => 'Не указано', '1' => 'Да', '0' => 'Нет'];
    $value = static fn (string $field, mixed $fallback = '') => old($field, data_get($psychologist, $field, $fallback));
    $booleanValue = static function (string $field) use ($psychologist): string {
        if (old($field) !== null) {
            return (string) old($field);
        }

        $current = data_get($psychologist, $field);

        return $current === null ? '' : ($current ? '1' : '0');
    };
@endphp

<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Фамилия" name="last_name" :error="$errors->first('last_name')"><x-ui.input name="last_name" :value="$value('last_name')" :error="$errors->has('last_name')" /></x-ui.form-field>
    <x-ui.form-field label="Имя" name="first_name" :error="$errors->first('first_name')"><x-ui.input name="first_name" :value="$value('first_name')" :error="$errors->has('first_name')" /></x-ui.form-field>
</div>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Отчество" name="middle_name" :error="$errors->first('middle_name')"><x-ui.input name="middle_name" :value="$value('middle_name')" :error="$errors->has('middle_name')" /></x-ui.form-field>
    <x-ui.form-field label="Телефон" name="phone" :error="$errors->first('phone')"><x-ui.input name="phone" type="tel" :value="$value('phone')" :error="$errors->has('phone')" /></x-ui.form-field>
</div>
<x-ui.form-field label="Email" name="email" required :error="$errors->first('email')"><x-ui.input name="email" type="email" :value="$value('email')" :error="$errors->has('email')" required /></x-ui.form-field>

<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Тип образования" name="education_type_id" :error="$errors->first('education_type_id')"><x-ui.select name="education_type_id" :value="(string) $value('education_type_id')" :options="['' => 'Не указано'] + $educationTypes" :error="$errors->has('education_type_id')" /></x-ui.form-field>
    <x-ui.form-field label="Другое образование" name="other_education" :error="$errors->first('other_education')"><x-ui.input name="other_education" :value="$value('other_education')" :error="$errors->has('other_education')" /></x-ui.form-field>
</div>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Модальность / программа" name="modality" :error="$errors->first('modality')"><x-ui.input name="modality" :value="$value('modality')" :error="$errors->has('modality')" /></x-ui.form-field>
    <x-ui.form-field label="Учебный центр" name="training_center" :error="$errors->first('training_center')"><x-ui.input name="training_center" :value="$value('training_center')" :error="$errors->has('training_center')" /></x-ui.form-field>
</div>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Год выпуска" name="graduation_year" :error="$errors->first('graduation_year')"><x-ui.input name="graduation_year" type="number" min="1900" :max="now()->year" :value="$value('graduation_year')" :error="$errors->has('graduation_year')" /></x-ui.form-field>
    <x-ui.form-field label="Часы обучения" name="training_hours" :error="$errors->first('training_hours')"><x-ui.input name="training_hours" type="number" min="0" :value="$value('training_hours')" :error="$errors->has('training_hours')" /></x-ui.form-field>
</div>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Номер лицензии" name="license_number" :error="$errors->first('license_number')"><x-ui.input name="license_number" :value="$value('license_number')" :error="$errors->has('license_number')" /></x-ui.form-field>
    <x-ui.form-field label="Лицензия действует до" name="license_expires_at" :error="$errors->first('license_expires_at')"><x-ui.input name="license_expires_at" type="date" :value="old('license_expires_at', $psychologist?->license_expires_at?->format('Y-m-d'))" :error="$errors->has('license_expires_at')" /></x-ui.form-field>
</div>
<x-ui.form-field label="Опыт ведения групп" name="group_leading_experience" :error="$errors->first('group_leading_experience')"><x-ui.textarea name="group_leading_experience" :error="$errors->has('group_leading_experience')">{{ $value('group_leading_experience') }}</x-ui.textarea></x-ui.form-field>
<x-ui.form-field label="Количество проведённых групп" name="groups_held_count" :error="$errors->first('groups_held_count')"><x-ui.input name="groups_held_count" type="number" min="0" :value="$value('groups_held_count')" :error="$errors->has('groups_held_count')" /></x-ui.form-field>

<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Подлинность документов подтверждена" name="documents_truth_confirmed" :error="$errors->first('documents_truth_confirmed')"><x-ui.select name="documents_truth_confirmed" :value="$booleanValue('documents_truth_confirmed')" :options="$booleanOptions" :error="$errors->has('documents_truth_confirmed')" /></x-ui.form-field>
    <x-ui.form-field label="Образование соответствует требованиям" name="education_compliance_confirmed" :error="$errors->first('education_compliance_confirmed')"><x-ui.select name="education_compliance_confirmed" :value="$booleanValue('education_compliance_confirmed')" :options="$booleanOptions" :error="$errors->has('education_compliance_confirmed')" /></x-ui.form-field>
</div>
<x-ui.form-field label="Готовность провести вебинар / эфир" name="ready_to_host_webinar" :error="$errors->first('ready_to_host_webinar')"><x-ui.select name="ready_to_host_webinar" :value="$booleanValue('ready_to_host_webinar')" :options="$booleanOptions" :error="$errors->has('ready_to_host_webinar')" /></x-ui.form-field>
<div class="ui-form-grid ui-form-grid--2">
    <x-ui.form-field label="Дата и время согласия на обработку данных" name="personal_data_consent_at" :error="$errors->first('personal_data_consent_at')"><x-ui.input name="personal_data_consent_at" type="datetime-local" :value="old('personal_data_consent_at', $psychologist?->personal_data_consent_at?->setTimezone(config('app.display_timezone'))->format('Y-m-d\\TH:i'))" :error="$errors->has('personal_data_consent_at')" /></x-ui.form-field>
    <x-ui.form-field label="Версия согласия" name="personal_data_consent_version" :error="$errors->first('personal_data_consent_version')"><x-ui.input name="personal_data_consent_version" :value="$value('personal_data_consent_version')" :error="$errors->has('personal_data_consent_version')" /></x-ui.form-field>
</div>
