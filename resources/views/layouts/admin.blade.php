@extends('layouts.app')

@section('content')
@php
    $psychologistsActive = request()->routeIs('admin.psychologists.*') ? ' is-active' : '';
    $groupsActive = request()->routeIs('admin.groups.*') ? ' is-active' : '';
    $navigation = new Illuminate\Support\HtmlString(
        '<div class="ui-nav-label">Администратор</div>'.
        '<a class="ui-nav-item'.$psychologistsActive.'" href="'.route('admin.psychologists.index').'"><i data-lucide="users"></i>Психологи</a>'.
        '<a class="ui-nav-item'.$groupsActive.'" href="'.route('admin.groups.index').'"><i data-lucide="folder"></i>Группы</a>'.
        '<form class="ui-nav-logout" method="POST" action="'.route('logout').'">'.csrf_field().'<button class="ui-nav-item" type="submit"><i data-lucide="log-out"></i>Выйти</button></form>'
    );
@endphp
<x-ui.app-shell :navigation="$navigation">
    @if(session('status'))<x-ui.alert class="ui-flash" variant="success" title="{{ session('status') }}" />@endif
    @if(session('error'))<x-ui.alert class="ui-flash" variant="danger" title="{{ session('error') }}" />@endif
    @yield('admin-content')
</x-ui.app-shell>
@endsection
