@props(['name' => 'document', 'label' => 'Выберите файл', 'accept' => '.pdf,.jpg,.jpeg,.png', 'error' => null])
<div {{ $attributes->class(['ui-file-upload', 'is-invalid' => $error]) }} data-ui-file-upload>
    <input class="visually-hidden" id="{{ $name }}" name="{{ $name }}" type="file" accept="{{ $accept }}" data-ui-file-input>
    <label for="{{ $name }}"><x-ui.icon name="upload" size="22" /><span data-ui-file-label>{{ $label }}</span><small>PDF, JPEG или PNG</small></label>
    @if($error)<div class="ui-field-error" id="{{ $name }}-error"><x-ui.icon name="alert-circle" size="13" />{{ $error }}</div>@endif
</div>
