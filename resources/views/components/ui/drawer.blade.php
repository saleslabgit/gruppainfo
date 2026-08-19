@props(['id', 'title', 'side' => 'left', 'footer' => null])
<div class="offcanvas offcanvas-{{ $side === 'right' ? 'end' : 'start' }} ui-drawer" tabindex="-1" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
    <header class="offcanvas-header"><strong id="{{ $id }}-title">{{ $title }}</strong><x-ui.icon-button label="Закрыть" icon="x" data-bs-dismiss="offcanvas" /></header>
    <div class="offcanvas-body">{{ $slot }}</div>
    @if($footer)<footer class="ui-drawer__footer">{{ $footer }}</footer>@endif
</div>
