@props(['name', 'type' => 'text', 'error' => false])
<input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class(['ui-input', 'is-invalid' => $error]) }}>
