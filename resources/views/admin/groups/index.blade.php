@extends('layouts.admin')
@section('title', 'Группы — Gruppa Info')
@section('description', 'Управление группами')
@section('admin-content')
@php
    $statuses = collect(App\Domain\Group\GroupStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $activeFilters = array_filter(['status' => $filters['status'] ?? null, 'tariff' => $filters['tariff'] ?? null, 'quick' => $filters['quick'] ?? null, 'sort' => $filters['sort'] ?? null, 'direction' => $filters['direction'] ?? null], fn ($value) => $value !== null && $value !== '');
@endphp
<div class="ui-list-page">
    <x-ui.page-header eyebrow="Администрирование" title="Группы" description="Поиск, модерация и публикация групп." />
    <div class="ui-table-wrap">
        <x-ui.table-toolbar>
            <form method="GET" action="{{ route('admin.groups.index') }}">@foreach($activeFilters as $name => $value)<input type="hidden" name="{{ $name }}" value="{{ $value }}">@endforeach<x-ui.search-input name="search" placeholder="ID, группа или психолог" value="{{ $filters['search'] ?? '' }}" /></form>
            <x-ui.filters id="group-filters" :action="route('admin.groups.index')" :active-count="count($activeFilters)">
                <x-slot:fields>@include('admin.groups._filters', ['suffix' => 'desktop'])</x-slot:fields>
                <x-slot:mobileFields>@include('admin.groups._filters', ['suffix' => 'mobile'])</x-slot:mobileFields>
            </x-ui.filters>
            <span class="ui-table-toolbar__count">Найдено: {{ $groups->total() }}</span>
            <x-ui.button class="ui-table-toolbar__action" href="{{ route('admin.groups.create') }}" size="small" icon="plus">Добавить</x-ui.button>
        </x-ui.table-toolbar>
        <x-ui.table :selectable="false" :headers="['ID / группа', 'Психолог', 'Статус', 'Тариф', 'Создана', 'Публикация', 'Завершение', '']">
            @forelse($groups as $group)
                <x-ui.table-row>
                    <div><a class="ui-table-primary" href="{{ route('admin.groups.show', $group) }}">#{{ $group->getKey() }} · {{ $group->name ?: 'Без названия' }}</a></div>
                    <div>{{ $group->owner->fullName() }}<span class="ui-table-secondary">{{ $group->owner->email }}</span></div>
                    <div><x-ui.badge :variant="$group->status->badgeVariant()">{{ $group->status->label() }}</x-ui.badge></div>
                    <div>{{ $group->free ? 'Бесплатный' : 'Платный' }}</div>
                    <div><x-ui.date :value="$group->created_at" /></div>
                    <div>@if($group->published_at)<x-ui.date :value="$group->published_at" />@else Не указано @endif</div>
                    <div>@if($group->expires_at)<x-ui.date :value="$group->expires_at" />@else Не указано @endif</div>
                    <div><x-ui.icon-button label="Открыть группу" icon="chevron-right" href="{{ route('admin.groups.show', $group) }}" /></div>
                </x-ui.table-row>
            @empty<div class="ui-table-empty">{{ request()->query() ? 'По заданным условиям группы не найдены.' : 'Групп пока нет.' }}</div>@endforelse
            <x-slot:footer><x-ui.pagination :paginator="$groups" /></x-slot:footer>
        </x-ui.table>
    </div>
</div>
@endsection
