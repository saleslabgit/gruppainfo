@props(['label', 'name', 'required' => false, 'helper' => null, 'error' => null, 'disabled' => false])
<div {{ $attributes->class('ui-field') }}>
    <label class="ui-label @if($disabled) is-disabled @endif" for="{{ $name }}">{{ $label }}@if($required)<span class="ui-required" aria-hidden="true">*</span>@endif</label>
    {{ $slot }}
    @if($error)<div class="ui-field-error" id="{{ $name }}-error"><x-ui.icon name="alert-circle" size="13" />{{ $error }}</div>@elseif($helper)<div class="ui-helper" id="{{ $name }}-helper">{{ $helper }}</div>@endif
</div>
