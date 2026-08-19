@props(['name', 'error' => false])
<textarea id="{{ $name }}" name="{{ $name }}" @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif {{ $attributes->class(['ui-textarea', 'is-invalid' => $error]) }}>{{ $slot }}</textarea>
