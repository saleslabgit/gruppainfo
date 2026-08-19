@props(['name', 'label', 'value', 'checked' => false])
<label class="ui-choice"><input class="ui-choice__native" type="radio" name="{{ $name }}" value="{{ $value }}" @checked($checked) {{ $attributes }}><span class="ui-radio" aria-hidden="true"></span><span>{{ $label }}</span></label>
