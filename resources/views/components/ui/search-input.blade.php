@props(['name' => 'search', 'label' => 'Поиск', 'placeholder' => 'Поиск'])
<label {{ $attributes->class('ui-search') }}><span class="visually-hidden">{{ $label }}</span><x-ui.icon name="search" size="17" /><input type="search" name="{{ $name }}" placeholder="{{ $placeholder }}"></label>
