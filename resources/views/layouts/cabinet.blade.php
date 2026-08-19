@extends('layouts.app')

@section('content')
@php
    $groupsActive = request()->routeIs('cabinet.groups*') ? ' is-active' : '';
    $profileActive = request()->routeIs('cabinet.profile') ? ' is-active' : '';
    $navigation = new Illuminate\Support\HtmlString(
        '<div class="ui-nav-label">Психолог</div>'.
        '<a class="ui-nav-item'.$groupsActive.'" href="'.route('cabinet.groups').'"><i data-lucide="users-round"></i>Мои группы</a>'.
        '<a class="ui-nav-item'.$profileActive.'" href="'.route('cabinet.profile').'"><i data-lucide="user-round"></i>Мои данные</a>'.
        '<form class="ui-nav-logout" method="POST" action="'.route('logout').'">'.csrf_field().'<button class="ui-nav-item" type="submit"><i data-lucide="log-out"></i>Выйти</button></form>'
    );
@endphp
<x-ui.app-shell :navigation="$navigation">
    @if(session('status'))<x-ui.alert class="ui-flash" variant="success" title="{{ session('status') }}" />@endif
    @if(session('error'))<x-ui.alert class="ui-flash" variant="danger" title="{{ session('error') }}" />@endif
    @yield('cabinet-content')
</x-ui.app-shell>
@endsection
