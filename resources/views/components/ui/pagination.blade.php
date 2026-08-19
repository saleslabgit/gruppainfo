@props(['paginator'])
@php
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $pageCandidates = match (true) {
        $currentPage <= 3 => [1, 2, 3, $lastPage],
        $currentPage >= $lastPage - 2 => [1, $lastPage - 2, $lastPage - 1, $lastPage],
        default => [1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage],
    };
    $pageNumbers = array_values(array_unique(array_filter($pageCandidates, fn (int $page): bool => $page >= 1 && $page <= $lastPage)));
    sort($pageNumbers);
@endphp
<nav aria-label="Пагинация" {{ $attributes->class('ui-pagination') }}>
    <span>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} из {{ $paginator->total() }}</span>
    <div class="ui-pagination__pages">
        @if($paginator->previousPageUrl())
            <a class="ui-pagination__control" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Предыдущая страница"><x-ui.icon name="chevron-left" size="15" /></a>
        @else
            <span class="ui-pagination__control is-disabled" aria-disabled="true" aria-label="Предыдущая страница"><x-ui.icon name="chevron-left" size="15" /></span>
        @endif
        @foreach($pageNumbers as $page)
            @if(!$loop->first && $page > $pageNumbers[$loop->index - 1] + 1)<span class="ui-pagination__ellipsis">…</span>@endif
            @if($page === $currentPage)
                <span class="ui-pagination__control is-current" aria-current="page">{{ $page }}</span>
            @else
                <a class="ui-pagination__control" href="{{ $paginator->url($page) }}" aria-label="Страница {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach
        @if($paginator->nextPageUrl())
            <a class="ui-pagination__control" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Следующая страница"><x-ui.icon name="chevron-right" size="15" /></a>
        @else
            <span class="ui-pagination__control is-disabled" aria-disabled="true" aria-label="Следующая страница"><x-ui.icon name="chevron-right" size="15" /></span>
        @endif
    </div>
</nav>
