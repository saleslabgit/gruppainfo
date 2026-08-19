@props(['label'])
<div class="ui-description-list__row"><dt>{{ $label }}</dt><dd>{{ $slot->isEmpty() ? 'Не указано' : $slot }}</dd></div>
