@props(['variant' => 'primary', 'size' => 'default', 'type' => 'button', 'icon' => null, 'iconPosition' => 'start', 'loading' => false, 'href' => null])
@php($tag = $href ? 'a' : 'button')
<{{ $tag }} @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif {{ $attributes->class(['ui-button', 'ui-button--'.$variant, 'ui-button--'.$size, 'is-loading' => $loading]) }}>
    @if($loading)<x-ui.icon name="loader-2" size="16" stroke="2" class="ui-spinner" />@elseif($icon && $iconPosition === 'start')<x-ui.icon :name="$icon" />@endif
    <span>{{ $slot }}</span>
    @if(!$loading && $icon && $iconPosition === 'end')<x-ui.icon :name="$icon" />@endif
</{{ $tag }}>
