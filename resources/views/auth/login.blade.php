@extends('layouts.app')

@section('title', 'Вход — Gruppa Info')
@section('description', 'Вход во внутренний кабинет Gruppa Info')

@section('content')
@php
    $navigation = new Illuminate\Support\HtmlString('<div class="ui-nav-label">Доступ</div><a class="ui-nav-item is-active" href="'.route('login').'"><i data-lucide="log-in"></i>Вход</a>');
@endphp
<x-ui.app-shell :navigation="$navigation">
    <x-ui.page-header eyebrow="Gruppa Info" title="Вход в систему" description="Используйте выданные вам данные учётной записи." />

    <x-ui.card variant="section" title="Учётные данные">
        <form class="ui-form" method="POST" action="{{ route('login.store') }}">
            @csrf
            @if(session('status'))
                <x-ui.alert variant="success" title="Готово">{{ session('status') }}</x-ui.alert>
            @endif
            @if(session('access_message'))
                <x-ui.alert variant="warning" title="Доступ завершён">{{ session('access_message') }}</x-ui.alert>
            @endif
            <x-ui.form-field label="Email" name="email" required :error="$errors->first('email')">
                <x-ui.input name="email" type="email" :value="old('email')" autocomplete="username" autofocus :error="$errors->has('email')" />
            </x-ui.form-field>
            <x-ui.form-field label="Пароль" name="password" required :error="$errors->first('password')">
                <x-ui.input name="password" type="password" autocomplete="current-password" :error="$errors->has('password')" />
            </x-ui.form-field>
            <div class="ui-form__actions">
                <x-ui.button type="submit" icon="log-in">Войти</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-ui.app-shell>
@endsection
