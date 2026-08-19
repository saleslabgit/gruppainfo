@extends('layouts.admin')

@section('title', $psychologist->fullName().' — Gruppa Info')
@section('description', 'Карточка психолога')

@section('admin-content')
@php
    $notSpecified = 'Не указано';
    $booleanLabel = static fn (?bool $value): string => match ($value) { true => 'Да', false => 'Нет', null => 'Не указано' };
@endphp
<x-ui.page-header eyebrow="Психологи" :title="$psychologist->fullName()" description="Анкетные данные, доступ и приватные документы.">
    <x-slot:actions><x-ui.button href="{{ route('admin.psychologists.edit', $psychologist) }}" variant="secondary" icon="pencil">Редактировать</x-ui.button></x-slot:actions>
</x-ui.page-header>

<div class="ui-detail-stack">
    <x-ui.card variant="section" title="Состояние">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Статус"><x-ui.badge :variant="$psychologist->status->badgeVariant()">{{ $psychologist->status->label() }}</x-ui.badge></x-ui.description-item>
            <x-ui.description-item label="Тариф">{{ $psychologist->free ? 'Бесплатный' : 'Платный' }}</x-ui.description-item>
            <x-ui.description-item label="Доступ"><x-ui.badge :variant="$psychologist->disabled ? 'danger' : 'success'">{{ $psychologist->disabled ? 'Отключён' : 'Включён' }}</x-ui.badge></x-ui.description-item>
            <x-ui.description-item label="Создан"><x-ui.date :value="$psychologist->created_at" /></x-ui.description-item>
            <x-ui.description-item label="Обновлён"><x-ui.date :value="$psychologist->updated_at" /></x-ui.description-item>
        </x-ui.description-list>
        <div class="ui-detail-actions">
            @if($psychologist->status === App\Domain\User\UserStatus::Pending)
                <x-ui.button data-bs-toggle="modal" data-bs-target="#approve-psychologist" icon="check">Принять</x-ui.button>
                <x-ui.button data-bs-toggle="modal" data-bs-target="#reject-psychologist" variant="danger" icon="x">Отклонить</x-ui.button>
            @endif
            <x-ui.button data-bs-toggle="modal" data-bs-target="#change-tariff" variant="secondary" icon="wallet-cards">Сменить тариф</x-ui.button>
            @if($psychologist->disabled)
                <x-ui.button data-bs-toggle="modal" data-bs-target="#enable-psychologist" variant="secondary" icon="unlock">Включить доступ</x-ui.button>
            @else
                <x-ui.button data-bs-toggle="modal" data-bs-target="#disable-psychologist" variant="danger" icon="lock">Отключить доступ</x-ui.button>
            @endif
            <x-ui.button data-bs-toggle="modal" data-bs-target="#delete-psychologist" variant="danger" icon="trash-2">Удалить</x-ui.button>
        </div>
    </x-ui.card>

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
        <form class="ui-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.psychologists.documents.store', $psychologist) }}">
            @csrf
            <x-ui.form-field label="Категория документа" name="type" required :error="$errors->first('type')"><x-ui.select name="type" :value="(string) old('type')" :options="App\Domain\User\UserDocumentType::options()" :error="$errors->has('type')" /></x-ui.form-field>
            <x-ui.file-upload name="document" label="Выберите или перетащите файл" :error="$errors->first('document')" />
            @if(config('documents.max_upload_kb'))<p class="ui-muted">Максимальный размер: {{ config('documents.max_upload_kb') }} КБ.</p>@endif
            <div class="ui-form__actions"><x-ui.button type="submit" icon="upload">Загрузить</x-ui.button></div>
        </form>
        @if($psychologist->documents->isEmpty())
            <p class="ui-muted">Документы пока не загружены.</p>
        @else
            <div class="ui-document-list">
                @foreach($psychologist->documents as $document)
                    <x-ui.document-item :name="$document->original_name" :meta="$document->type->label().' · '.number_format($document->size / 1024, 1, ',', ' ').' КБ'" :view-href="route('documents.view', $document)" :download-href="route('documents.download', $document)">
                        <x-slot:actions><button class="ui-danger-link" type="button" data-bs-toggle="modal" data-bs-target="#delete-document-{{ $document->getKey() }}">Удалить</button></x-slot:actions>
                    </x-ui.document-item>
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>

@if($psychologist->status === App\Domain\User\UserStatus::Pending)
<x-ui.modal id="approve-psychologist" title="Принять психолога" size="small">
    <p>Статус психолога изменится на «Принят».</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.approve', $psychologist) }}">@csrf<x-ui.button type="submit">Принять</x-ui.button></form></x-slot:footer>
</x-ui.modal>
<x-ui.modal id="reject-psychologist" title="Отклонить психолога" size="small">
    <p>Статус изменится на «Отклонён», все активные сессии будут завершены.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.reject', $psychologist) }}">@csrf<x-ui.button type="submit" variant="danger">Отклонить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@endif
<x-ui.modal id="change-tariff" title="Сменить тариф" size="small">
    <p>Новый тариф: {{ $psychologist->free ? 'платный' : 'бесплатный' }}. Существующие группы не изменятся.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.tariff', $psychologist) }}">@csrf @method('PATCH')<input type="hidden" name="free" value="{{ $psychologist->free ? 0 : 1 }}"><x-ui.button type="submit">Сменить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@if($psychologist->disabled)
<x-ui.modal id="enable-psychologist" title="Включить доступ" size="small">
    <p>Психолог снова сможет войти при наличии пароля и принятого статуса.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.enable', $psychologist) }}">@csrf<x-ui.button type="submit">Включить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@else
<x-ui.modal id="disable-psychologist" title="Отключить доступ" size="small">
    <p>Все активные сессии психолога будут немедленно завершены.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.disable', $psychologist) }}">@csrf<x-ui.button type="submit" variant="danger">Отключить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@endif
<x-ui.modal id="delete-psychologist" title="Удалить психолога" size="small">
    <p>Психолог исчезнет из списка, его сессии завершатся. Документы и история сохранятся.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.destroy', $psychologist) }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="danger">Удалить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@foreach($psychologist->documents as $document)
<x-ui.modal id="delete-document-{{ $document->getKey() }}" title="Удалить документ" size="small">
    <p>Файл «{{ $document->original_name }}» будет удалён без возможности восстановления.</p>
    <x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.psychologists.documents.destroy', [$psychologist, $document]) }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="danger">Удалить</x-ui.button></form></x-slot:footer>
</x-ui.modal>
@endforeach
@endsection
