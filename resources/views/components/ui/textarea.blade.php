@props(['name', 'id' => null, 'error' => false])
<textarea id="{{ $id ?? $name }}" name="{{ $name }}" @if($error) aria-invalid="true" aria-describedby="{{ $id ?? $name }}-error" @endif {{ $attributes->class(['ui-textarea', 'is-invalid' => $error]) }}>{{ $slot }}</textarea>
