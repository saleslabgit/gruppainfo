@props(['title', 'message' => null, 'inline' => false])
@if($inline)<div role="alert" {{ $attributes->class('ui-block-error') }}>{{ $title }}</div>@else<div role="alert" {{ $attributes->class('ui-error-state') }}><x-ui.icon name="server-crash" size="28" /><strong>{{ $title }}</strong>@if($message)<span>{{ $message }}</span>@endif{{ $slot }}</div>@endif
