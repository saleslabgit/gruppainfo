@props(['eyebrow' => null, 'title', 'description' => null, 'actions' => null])
<header {{ $attributes->class('ui-page-header') }}><div>@if($eyebrow)<div class="ui-page-header__eyebrow">{{ $eyebrow }}</div>@endif<h1>{{ $title }}</h1>@if($description)<p>{{ $description }}</p>@endif</div>@if($actions)<div class="ui-page-header__actions">{{ $actions }}</div>@endif</header>
