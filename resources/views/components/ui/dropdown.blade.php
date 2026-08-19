@props(['label' => 'Открыть меню', 'icon' => 'ellipsis'])
<div class="dropdown"><x-ui.icon-button :label="$label" :icon="$icon" data-bs-toggle="dropdown" aria-expanded="false" /><div {{ $attributes->class('dropdown-menu ui-dropdown') }}>{{ $slot }}</div></div>
