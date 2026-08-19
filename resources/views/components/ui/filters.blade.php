@props(['id', 'action', 'activeCount' => 0])
<div class="ui-filters">
    <button class="ui-filter-trigger ui-filters__desktop-trigger" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"><x-ui.icon name="filter" size="14" />Фильтры@if($activeCount)<span>{{ $activeCount }}</span>@endif</button>
    <div class="dropdown-menu ui-filter-popover">
        <form class="ui-filter-panel" method="GET" action="{{ $action }}">
            {{ $fields }}
            <div class="ui-filter-panel__footer"><a href="{{ $action }}">Сбросить</a><x-ui.button type="submit" size="small">Применить</x-ui.button></div>
        </form>
    </div>
    <button class="ui-filter-trigger ui-filters__mobile-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#{{ $id }}" aria-controls="{{ $id }}"><x-ui.icon name="filter" size="14" />Фильтры@if($activeCount)<span>{{ $activeCount }}</span>@endif</button>
    <x-ui.drawer :id="$id" title="Фильтры" side="right">
        <form class="ui-filter-panel ui-filter-panel--drawer" method="GET" action="{{ $action }}">
            {{ $mobileFields ?? $fields }}
            <div class="ui-filter-panel__footer"><a href="{{ $action }}">Сбросить</a><x-ui.button type="submit">Применить</x-ui.button></div>
        </form>
    </x-ui.drawer>
</div>
