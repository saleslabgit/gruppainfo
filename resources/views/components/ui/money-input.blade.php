@props(['name', 'currency' => 'BYN', 'error' => false])
<div @class(['ui-money-input', 'is-invalid' => $error])>
    <input id="{{ $name }}" name="{{ $name }}" type="text" inputmode="decimal" @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class('ui-money-input__control') }}>
    <span class="ui-money-input__currency">{{ $currency }}</span>
</div>
