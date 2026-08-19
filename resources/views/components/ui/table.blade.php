@props(['headers' => [], 'selectable' => true, 'footer' => null])
<div {{ $attributes->class(['ui-table', 'ui-table--no-select' => !$selectable]) }}><div class="ui-table__scroll"><div class="ui-table__header" role="row">@foreach($headers as $header)<div role="columnheader">{{ $header }}</div>@endforeach</div><div class="ui-table__body">{{ $slot }}</div></div>@if($footer){{ $footer }}@endif</div>
