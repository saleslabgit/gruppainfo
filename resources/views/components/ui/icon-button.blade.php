@props(['label', 'icon', 'size' => 'default', 'variant' => 'default', 'type' => 'button', 'href' => null])
@php($tag = $href ? 'a' : 'button')
<{{ $tag }} @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class(['ui-icon-button', 'ui-icon-button--'.$size, 'ui-icon-button--'.$variant]) }}><x-ui.icon :name="$icon" /></{{ $tag }}>
