@props(['name', 'label', 'checked' => false])
<label class="ui-choice"><input class="ui-choice__native" type="checkbox" name="{{ $name }}" @checked($checked) {{ $attributes }}><span class="ui-checkbox" aria-hidden="true"><x-ui.icon name="check" size="13" stroke="2.5" /></span><span>{{ $label }}</span></label>
