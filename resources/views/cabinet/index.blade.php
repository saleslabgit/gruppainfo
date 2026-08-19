@extends('layouts.app')

@section('title', 'Кабинет психолога — Gruppa Info')
@section('description', 'Защищённая область психолога Gruppa Info')

@section('content')
@php
    $navigation = new Illuminate\Support\HtmlString('<div class="ui-nav-label">Психолог</div><a class="ui-nav-item is-active" href="'.route('cabinet.index').'"><i data-lucide="user-round"></i>Обзор</a>');
@endphp
<x-ui.app-shell :navigation="$navigation">
    <x-ui.page-header eyebrow="Психолог" title="Защищённая область" description="Минимальная страница проверки авторизации и разграничения доступа.">
        <x-slot:actions>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" icon="log-out">Выйти</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card variant="section" title="Текущая учётная запись">
        <p><strong>{{ auth()->user()->email }}</strong></p>
        <p>Роль: психолог</p>
    </x-ui.card>
</x-ui.app-shell>
@endsection
