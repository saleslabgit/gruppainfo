@extends('layouts.cabinet')
@section('title', ($group->name ?: 'Группа').' — Gruppa Info')
@section('description', 'Карточка группы')
@section('cabinet-content')
<x-ui.page-header eyebrow="Мои группы" :title="$group->name ?: 'Без названия'" description="Данные группы, модерация и история статусов.">
    <x-slot:actions>
        @can('update', $group)<x-ui.button href="{{ route('cabinet.groups.edit', $group) }}" variant="secondary" icon="pencil">Редактировать</x-ui.button>@endcan
    </x-slot:actions>
</x-ui.page-header>
<div class="ui-detail-stack">
    @if($errors->any())
        <x-ui.alert variant="danger" title="Группа не отправлена на модерацию">
            <ul>
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
            @can('update', $group)<x-ui.button href="{{ route('cabinet.groups.edit', $group) }}" variant="secondary" size="small">Перейти к редактированию</x-ui.button>@endcan
        </x-ui.alert>
    @endif
    @if($group->status === App\Domain\Group\GroupStatus::Revision && $group->moderator_comment)<x-ui.alert variant="warning" title="Требуется доработка">{{ $group->moderator_comment }}</x-ui.alert>@endif
    @if($group->status === App\Domain\Group\GroupStatus::Rejected && $group->rejection_reason)<x-ui.alert variant="danger" title="Группа отклонена">{{ $group->rejection_reason }}</x-ui.alert>@endif
    <x-ui.card variant="section" title="Группа">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Статус"><x-ui.badge :variant="$group->status->badgeVariant()">{{ $group->status->label() }}</x-ui.badge></x-ui.description-item>
            <x-ui.description-item label="Название">{{ $group->name ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Формат">{{ $group->format?->name ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Пол участников">{{ $group->gender?->name ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Длительность">{{ $group->meeting_duration_minutes ? $group->meeting_duration_minutes.' мин.' : 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Участников">{{ $group->participant_count ?? 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Стоимость">@if($group->price_per_meeting !== null)<x-ui.money :minor-units="$group->price_per_meeting" />@else Не указано @endif</x-ui.description-item>
            <x-ui.description-item label="Создана"><x-ui.date :value="$group->created_at" /></x-ui.description-item>
            <x-ui.description-item label="Опубликована">@if($group->published_at)<x-ui.date :value="$group->published_at" />@else Не указано @endif</x-ui.description-item>
            <x-ui.description-item label="Завершение">@if($group->expires_at)<x-ui.date :value="$group->expires_at" />@else Не указано @endif</x-ui.description-item>
            <x-ui.description-item label="ID каталога">{{ $group->external_catalog_id ?: 'Не указано' }}</x-ui.description-item>
        </x-ui.description-list>
        <x-ui.description-list><x-ui.description-item label="Расписание">{{ $group->schedule ?: 'Не указано' }}</x-ui.description-item><x-ui.description-item label="Описание">{{ $group->description ?: 'Не указано' }}</x-ui.description-item></x-ui.description-list>
        <div class="ui-detail-actions">
            @can('submit', $group)<form method="POST" action="{{ route('cabinet.groups.submit', $group) }}">@csrf<x-ui.button type="submit" icon="send">Отправить на модерацию</x-ui.button></form>@endcan
            @can('delete', $group)<x-ui.button data-bs-toggle="modal" data-bs-target="#delete-group" variant="danger" icon="trash-2">Удалить</x-ui.button>@endcan
        </div>
    </x-ui.card>
    @include('groups._history')
</div>
@can('delete', $group)<x-ui.modal id="delete-group" title="Удалить группу" size="small"><p>Группа будет удалена, история сохранится.</p><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('cabinet.groups.destroy', $group) }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="danger">Удалить</x-ui.button></form></x-slot:footer></x-ui.modal>@endcan
@endsection
