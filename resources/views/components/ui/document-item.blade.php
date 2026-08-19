@props(['name', 'meta', 'viewHref' => null, 'downloadHref' => null, 'actions' => null])
<article {{ $attributes->class('ui-document-item') }}>
    <div class="ui-document-item__icon"><x-ui.icon name="file-text" size="16" /></div>
    <div class="ui-document-item__content"><div>{{ $name }}</div><small>{{ $meta }}</small></div>
    <div class="ui-document-item__actions">
        @if($viewHref)<a href="{{ $viewHref }}" target="_blank" rel="noopener">Открыть</a>@endif
        @if($downloadHref)<a href="{{ $downloadHref }}">Скачать</a>@endif
        {{ $actions }}
    </div>
</article>
