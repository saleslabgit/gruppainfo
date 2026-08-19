@extends('layouts.admin')

@section('title', 'Новый психолог — Gruppa Info')
@section('description', 'Создание психолога в административной области')

@section('admin-content')
<x-ui.page-header eyebrow="Психологи" title="Новый психолог" description="Создайте внутреннюю запись без пароля и отправки email." />
<x-ui.card variant="section" title="Данные психолога">
    <form class="ui-form" method="POST" action="{{ route('admin.psychologists.store') }}">
        @csrf
        @include('admin.psychologists._form')
        <x-ui.form-field label="Начальный тариф" name="free" required :error="$errors->first('free')"><x-ui.select name="free" :value="(string) old('free', '0')" :options="['1' => 'Бесплатный', '0' => 'Платный']" :error="$errors->has('free')" /></x-ui.form-field>
        <div class="ui-form__actions"><x-ui.button href="{{ route('admin.psychologists.index') }}" variant="secondary">Отмена</x-ui.button><x-ui.button type="submit">Создать</x-ui.button></div>
    </form>
</x-ui.card>
@endsection
