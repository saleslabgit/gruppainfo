@props(['selected' => false, 'disabled' => false, 'removable' => false, 'href' => null])
@php($tag = $href ? 'a' : 'span')
<{{ $tag }} @if($href) href="{{ $href }}" @endif {{ $attributes->class(['ui-chip', 'is-selected' => $selected, 'is-disabled' => $disabled]) }}>{{ $slot }}@if($removable)<x-ui.icon name="x" size="13" />@endif</{{ $tag }}>
