@extends('layouts.app')

@section('title', 'Администрирование — Gruppa Info')
@section('description', 'Защищённая административная область Gruppa Info')

@section('content')
@php
    $navigation = new Illuminate\Support\HtmlString('<div class="ui-nav-label">Администратор</div><a class="ui-nav-item is-active" href="'.route('admin.index').'"><i data-lucide="shield-check"></i>Обзор</a>');
@endphp
<x-ui.app-shell :navigation="$navigation">
    <x-ui.page-header eyebrow="Администратор" title="Защищённая область" description="Минимальная страница проверки авторизации и разграничения доступа.">
        <x-slot:actions>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-ui.button type="submit" variant="secondary" icon="log-out">Выйти</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card variant="section" title="Текущая учётная запись">
        <p><strong>{{ auth()->user()->email }}</strong></p>
        <p>Роль: администратор</p>
    </x-ui.card>
</x-ui.app-shell>
@endsection
