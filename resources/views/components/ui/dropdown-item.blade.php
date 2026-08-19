@props(['icon' => null, 'danger' => false, 'disabled' => false])
<button type="button" @disabled($disabled) {{ $attributes->class(['dropdown-item ui-dropdown__item', 'is-danger' => $danger]) }}>@if($icon)<x-ui.icon :name="$icon" size="16" />@endif<span>{{ $slot }}</span></button>
