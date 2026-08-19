@props(['name', 'type' => 'text', 'error' => false])
@if($type === 'password')
    <div @class(['ui-input-shell', 'is-invalid' => $error, 'is-readonly' => $attributes->has('readonly'), 'is-disabled' => $attributes->has('disabled')]) data-ui-password>
        <input id="{{ $name }}" name="{{ $name }}" type="password" @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class('ui-input ui-input--embedded') }} data-ui-password-input>
        <button class="ui-password-toggle" type="button" aria-label="Показать пароль" aria-pressed="false" @disabled($attributes->has('disabled')) data-ui-password-toggle><x-ui.icon name="eye" size="18" /></button>
    </div>
@else
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class(['ui-input', 'is-invalid' => $error]) }}>
@endif
