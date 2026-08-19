@extends('layouts.cabinet')

@section('title', 'Мои данные — Gruppa Info')
@section('description', 'Анкетные данные психолога')

@section('cabinet-content')
@php
    $notSpecified = 'Не указано';
    $booleanLabel = static fn (?bool $value): string => match ($value) { true => 'Да', false => 'Нет', null => 'Не указано' };
@endphp
<x-ui.page-header eyebrow="Кабинет психолога" title="Мои данные" description="Анкетные данные и приватные документы доступны только для просмотра." />

<div class="ui-detail-stack">
    <x-ui.card variant="section" title="Контакты и образование">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Фамилия">{{ $psychologist->last_name ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Имя">{{ $psychologist->first_name ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Отчество">{{ $psychologist->middle_name ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Email">{{ $psychologist->email }}</x-ui.description-item>
            <x-ui.description-item label="Телефон">{{ $psychologist->phone ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Тип образования">{{ $psychologist->educationType?->name ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Другое образование">{{ $psychologist->other_education ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Модальность / программа">{{ $psychologist->modality ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Учебный центр">{{ $psychologist->training_center ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Год выпуска">{{ $psychologist->graduation_year ?? $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Часы обучения">{{ $psychologist->training_hours ?? $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Номер лицензии">{{ $psychologist->license_number ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Лицензия действует до">{{ $psychologist->license_expires_at?->format('d.m.Y') ?? $notSpecified }}</x-ui.description-item>
        </x-ui.description-list>
    </x-ui.card>

    <x-ui.card variant="section" title="Опыт и подтверждения">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Опыт ведения групп">{{ $psychologist->group_leading_experience ?: $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Проведено групп">{{ $psychologist->groups_held_count ?? $notSpecified }}</x-ui.description-item>
            <x-ui.description-item label="Подлинность документов">{{ $booleanLabel($psychologist->documents_truth_confirmed) }}</x-ui.description-item>
            <x-ui.description-item label="Соответствие образования">{{ $booleanLabel($psychologist->education_compliance_confirmed) }}</x-ui.description-item>
            <x-ui.description-item label="Готовность к вебинару / эфиру">{{ $booleanLabel($psychologist->ready_to_host_webinar) }}</x-ui.description-item>
            <x-ui.description-item label="Согласие на обработку данных">@if($psychologist->personal_data_consent_at)<x-ui.date :value="$psychologist->personal_data_consent_at" />@else{{ $notSpecified }}@endif</x-ui.description-item>
            <x-ui.description-item label="Версия согласия">{{ $psychologist->personal_data_consent_version ?: $notSpecified }}</x-ui.description-item>
        </x-ui.description-list>
    </x-ui.card>

    <x-ui.card variant="section" title="Приватные документы">
        @if($psychologist->documents->isEmpty())
            <x-ui.empty-state icon="file-text" title="Документов пока нет" text="Загруженные документы появятся в этом разделе." />
        @else
            <div class="ui-document-list">
                @foreach($psychologist->documents as $document)
                    <x-ui.document-item :name="$document->original_name" :meta="$document->type->label().' · '.number_format($document->size / 1024, 1, ',', ' ').' КБ'" :view-href="route('documents.view', $document)" :download-href="route('documents.download', $document)" />
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>
@endsection
