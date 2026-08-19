@props(['icon' => 'inbox', 'title', 'text'])
<div {{ $attributes->class('ui-empty-state') }}><x-ui.icon :name="$icon" size="30" stroke="1.5" /><div class="ui-empty-state__title">{{ $title }}</div><div class="ui-empty-state__text">{{ $text }}</div>{{ $slot }}</div>
