@extends('layouts.cabinet')
@section('title', 'Мои группы — Gruppa Info')
@section('description', 'Группы психолога')
@section('cabinet-content')
<div class="ui-list-page">
    <x-ui.page-header eyebrow="Кабинет психолога" title="Мои группы" description="Создание групп и отслеживание модерации." />
    <div class="ui-table-wrap">
        <x-ui.table-toolbar>
            <span class="ui-table-toolbar__count">Всего: {{ $groups->total() }}</span>
            <form class="ui-table-toolbar__action" method="POST" action="{{ route('cabinet.groups.store') }}">@csrf<x-ui.button type="submit" size="small" icon="plus">Добавить группу</x-ui.button></form>
        </x-ui.table-toolbar>
        <x-ui.table :selectable="false" :headers="['Группа', 'Статус', 'Формат', 'Создана', 'Публикация', 'Завершение', '']">
            @forelse($groups as $group)
                <x-ui.table-row>
                    <div><a class="ui-table-primary" href="{{ route('cabinet.groups.show', $group) }}">{{ $group->name ?: 'Без названия' }}</a></div>
                    <div><x-ui.badge :variant="$group->status->badgeVariant()">{{ $group->status->label() }}</x-ui.badge></div>
                    <div>{{ $group->format?->name ?: 'Не указано' }}</div>
                    <div><x-ui.date :value="$group->created_at" /></div>
                    <div>@if($group->published_at)<x-ui.date :value="$group->published_at" />@else Не указано @endif</div>
                    <div>@if($group->expires_at)<x-ui.date :value="$group->expires_at" />@else Не указано @endif</div>
                    <div><x-ui.icon-button label="Открыть группу" icon="chevron-right" href="{{ route('cabinet.groups.show', $group) }}" /></div>
                </x-ui.table-row>
            @empty
                <div class="ui-table-empty"><x-ui.empty-state icon="users-round" title="Пока нет групп" text="Создайте первый черновик группы."><form method="POST" action="{{ route('cabinet.groups.store') }}">@csrf<x-ui.button type="submit" icon="plus">Добавить группу</x-ui.button></form></x-ui.empty-state></div>
            @endforelse
            <x-slot:footer><x-ui.pagination :paginator="$groups" /></x-slot:footer>
        </x-ui.table>
    </div>
</div>
@endsection
