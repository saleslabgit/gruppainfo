@props(['label', 'icon', 'size' => 'default', 'variant' => 'default', 'type' => 'button'])
<button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class(['ui-icon-button', 'ui-icon-button--'.$size, 'ui-icon-button--'.$variant]) }}><x-ui.icon :name="$icon" /></button>
