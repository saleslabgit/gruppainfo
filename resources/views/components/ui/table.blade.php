@props(['headers' => []])
<div {{ $attributes->class('ui-table') }}><div class="ui-table__scroll"><div class="ui-table__header" role="row">@foreach($headers as $header)<div role="columnheader">{{ $header }}</div>@endforeach</div><div class="ui-table__body">{{ $slot }}</div></div></div>
