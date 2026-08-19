@props(['columns' => 1])
<dl {{ $attributes->class(['ui-description-list', 'ui-description-list--two' => $columns === 2]) }}>{{ $slot }}</dl>
