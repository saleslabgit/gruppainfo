@props(['label' => 'Загрузка'])
<div role="status" {{ $attributes->class('ui-loading') }}><x-ui.icon name="loader-2" size="24" stroke="2" class="ui-spinner" /><span class="visually-hidden">{{ $label }}</span></div>
