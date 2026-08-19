@props(['title', 'meta', 'variant' => 'neutral', 'comment' => null])
<div class="ui-timeline__item">
    <div class="ui-timeline__rail"><span class="ui-timeline__marker ui-timeline__marker--{{ $variant }}"></span><span class="ui-timeline__connector"></span></div>
    <div class="ui-timeline__content"><div class="ui-timeline__title">{{ $title }}</div><div class="ui-timeline__meta">{{ $meta }}</div>@if($comment)<div class="ui-timeline__comment">{{ $comment }}</div>@endif</div>
</div>
