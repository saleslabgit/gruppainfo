@extends('layouts.admin')
@section('title', 'Новая группа — Gruppa Info')
@section('description', 'Создание группы')
@section('admin-content')
<x-ui.page-header eyebrow="Группы" title="Новая группа" description="Создайте черновик для существующего активного психолога." />
<x-ui.card><form class="ui-form" method="POST" action="{{ route('admin.groups.store') }}">@csrf
    <x-ui.form-field label="Психолог" name="owner_id" required :error="$errors->first('owner_id')"><x-ui.select name="owner_id" :value="(string) old('owner_id')" :options="['' => 'Выберите психолога'] + $owners" :error="$errors->has('owner_id')" /></x-ui.form-field>
    @include('groups._form')
    <div class="ui-form__actions"><x-ui.button href="{{ route('admin.groups.index') }}" variant="secondary">Отмена</x-ui.button><x-ui.button type="submit">Создать черновик</x-ui.button></div>
</form></x-ui.card>
@endsection
