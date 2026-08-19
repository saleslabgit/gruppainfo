@props(['variant' => 'info', 'title', 'icon' => null])
@php($icons = ['info' => 'info', 'success' => 'circle-check', 'warning' => 'triangle-alert', 'danger' => 'circle-alert'])
<div role="alert" {{ $attributes->class(['ui-alert', 'ui-alert--'.$variant]) }}><x-ui.icon :name="$icon ?? $icons[$variant]" size="19" /><div class="ui-alert__content"><div class="ui-alert__title">{{ $title }}</div>@if($slot->isNotEmpty())<div class="ui-alert__body">{{ $slot }}</div>@endif</div></div>
