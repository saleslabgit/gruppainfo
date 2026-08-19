@props(['name', 'value' => '', 'placeholder' => 'Выберите значение', 'options' => [], 'disabled' => false, 'error' => false])
<div class="ui-select" data-ui-select>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" data-ui-select-value>
    <button id="{{ $name }}" type="button" aria-haspopup="listbox" aria-expanded="false" @disabled($disabled) {{ $attributes->class(['ui-select__trigger', 'is-placeholder' => !$value, 'is-invalid' => $error]) }} data-ui-select-trigger><span data-ui-select-label>{{ $options[$value] ?? $placeholder }}</span><x-ui.icon name="chevron-down" size="16" /></button>
    <div class="ui-select__panel" role="listbox" hidden data-ui-select-panel>
        @foreach($options as $optionValue => $optionLabel)<button type="button" role="option" aria-selected="{{ $value === (string) $optionValue ? 'true' : 'false' }}" data-value="{{ $optionValue }}" @class(['ui-select__option', 'is-selected' => $value === (string) $optionValue])><span>{{ $optionLabel }}</span><x-ui.icon name="check" size="14" /></button>@endforeach
    </div>
</div>
