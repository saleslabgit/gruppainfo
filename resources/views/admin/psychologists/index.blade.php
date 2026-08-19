@extends('layouts.admin')

@section('title', 'Психологи — Gruppa Info')
@section('description', 'Управление психологами')

@section('admin-content')
@php
    $activeFilters = array_filter([
        'status' => $filters['status'] ?? null,
        'tariff' => $filters['tariff'] ?? null,
        'access' => $filters['access'] ?? null,
    ], static fn ($value) => $value !== null && $value !== '');
    $statusLabels = collect(App\Domain\User\UserStatus::cases())->mapWithKeys(fn ($status) => [$status->value => $status->label()])->all();
    $filterLabels = [
        'status' => $statusLabels,
        'tariff' => ['free' => 'Бесплатный', 'paid' => 'Платный'],
        'access' => ['enabled' => 'Включён', 'disabled' => 'Отключён'],
    ];
@endphp
<div class="ui-list-page">
    <x-ui.page-header eyebrow="Администрирование" title="Психологи" description="Поиск, просмотр и управление анкетами психологов." />

    <div class="ui-table-wrap">
    <x-ui.table-toolbar>
        <form method="GET" action="{{ route('admin.psychologists.index') }}">
            @foreach($activeFilters as $name => $filter)<input type="hidden" name="{{ $name }}" value="{{ $filter }}">@endforeach
            <x-ui.search-input name="search" placeholder="ФИО, email или телефон" value="{{ $filters['search'] ?? '' }}" />
        </form>
        <x-ui.filters id="psychologist-filters" :action="route('admin.psychologists.index')" :active-count="count($activeFilters)">
            <x-slot:fields>
                @if(isset($filters['search']))<input type="hidden" name="search" value="{{ $filters['search'] }}">@endif
                <x-ui.form-field label="Статус" name="status-filter-desktop"><x-ui.select name="status" id="status-filter-desktop" :value="(string) ($filters['status'] ?? '')" :options="['' => 'Все статусы'] + $statusLabels" /></x-ui.form-field>
                <x-ui.form-field label="Тариф" name="tariff-filter-desktop"><x-ui.select name="tariff" id="tariff-filter-desktop" :value="(string) ($filters['tariff'] ?? '')" :options="['' => 'Все тарифы', 'free' => 'Бесплатный', 'paid' => 'Платный']" /></x-ui.form-field>
                <x-ui.form-field label="Доступ" name="access-filter-desktop"><x-ui.select name="access" id="access-filter-desktop" :value="(string) ($filters['access'] ?? '')" :options="['' => 'Любой', 'enabled' => 'Включён', 'disabled' => 'Отключён']" /></x-ui.form-field>
            </x-slot:fields>
            <x-slot:mobileFields>
                @if(isset($filters['search']))<input type="hidden" name="search" value="{{ $filters['search'] }}">@endif
                <x-ui.form-field label="Статус" name="status-filter-mobile"><x-ui.select name="status" id="status-filter-mobile" :value="(string) ($filters['status'] ?? '')" :options="['' => 'Все статусы'] + $statusLabels" /></x-ui.form-field>
                <x-ui.form-field label="Тариф" name="tariff-filter-mobile"><x-ui.select name="tariff" id="tariff-filter-mobile" :value="(string) ($filters['tariff'] ?? '')" :options="['' => 'Все тарифы', 'free' => 'Бесплатный', 'paid' => 'Платный']" /></x-ui.form-field>
                <x-ui.form-field label="Доступ" name="access-filter-mobile"><x-ui.select name="access" id="access-filter-mobile" :value="(string) ($filters['access'] ?? '')" :options="['' => 'Любой', 'enabled' => 'Включён', 'disabled' => 'Отключён']" /></x-ui.form-field>
            </x-slot:mobileFields>
        </x-ui.filters>
        @if($activeFilters)
            <div class="ui-active-filters">
                @foreach($activeFilters as $name => $filter)
                    @php($remaining = array_filter(array_merge($filters, [$name => null]), static fn ($value) => $value !== null && $value !== ''))
                    <x-ui.chip selected removable href="{{ route('admin.psychologists.index', $remaining) }}">{{ $filterLabels[$name][$filter] }}</x-ui.chip>
                @endforeach
            </div>
        @endif
        <span class="ui-table-toolbar__count">Найдено: {{ $psychologists->total() }}</span>
        <x-ui.button class="ui-table-toolbar__action" href="{{ route('admin.psychologists.create') }}" size="small" icon="plus">Добавить</x-ui.button>
    </x-ui.table-toolbar>

    <x-ui.table :selectable="false" :headers="['Психолог', 'Телефон', 'Статус', 'Тариф', 'Доступ', 'Регистрация', '']">
        @if($psychologists->isEmpty())
            <div class="ui-table-empty">
                @if(request()->hasAny(['search', 'status', 'tariff', 'access']))
                    По заданным условиям психологи не найдены.
                @else
                    Психологов пока нет.
                @endif
            </div>
        @else
            @foreach($psychologists as $psychologist)
                <x-ui.table-row>
                    <div><a class="ui-table-primary" href="{{ route('admin.psychologists.show', $psychologist) }}">{{ $psychologist->fullName() }}</a><span class="ui-table-secondary">{{ $psychologist->email }}</span></div>
                    <div>{{ $psychologist->phone ?: 'Не указано' }}</div>
                    <div><x-ui.badge :variant="$psychologist->status->badgeVariant()">{{ $psychologist->status->label() }}</x-ui.badge></div>
                    <div>{{ $psychologist->free ? 'Бесплатный' : 'Платный' }}</div>
                    <div><x-ui.badge :variant="$psychologist->disabled ? 'danger' : 'success'">{{ $psychologist->disabled ? 'Отключён' : 'Включён' }}</x-ui.badge></div>
                    <div><x-ui.date :value="$psychologist->created_at" /></div>
                    <div><x-ui.icon-button label="Открыть карточку" icon="chevron-right" href="{{ route('admin.psychologists.show', $psychologist) }}" /></div>
                </x-ui.table-row>
            @endforeach
        @endif
        <x-slot:footer><x-ui.pagination :paginator="$psychologists" /></x-slot:footer>
    </x-ui.table>
    </div>
</div>
@endsection
