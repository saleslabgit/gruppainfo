@props(['title', 'message', 'danger' => false, 'actions' => null])
<div {{ $attributes->class('ui-confirmation') }}><strong>{{ $title }}</strong><p>{{ $message }}</p><div class="ui-confirmation__actions">@if($actions){{ $actions }}@else<x-ui.button variant="secondary">Отмена</x-ui.button><x-ui.button :variant="$danger ? 'danger' : 'primary'">Подтвердить</x-ui.button>@endif</div></div>
