@props(['variant' => 'basic', 'title' => null, 'footer' => null])
<article {{ $attributes->class(['ui-card', 'ui-card--'.$variant]) }}>@if($title)<header class="ui-card__header">{{ $title }}</header>@endif<div class="ui-card__body">{{ $slot }}</div>@if($footer)<footer class="ui-card__footer">{{ $footer }}</footer>@endif</article>
