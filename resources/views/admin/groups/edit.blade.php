@extends('layouts.admin')
@section('title', 'Редактирование группы — Gruppa Info')
@section('description', 'Данные группы')
@section('admin-content')
<x-ui.page-header eyebrow="Группы" :title="$group->name ?: 'Без названия'" description="Редактирование содержания не меняет статус группы." />
<x-ui.card><form class="ui-form" method="POST" action="{{ route('admin.groups.update', $group) }}">@csrf @method('PUT')
    @include('groups._form')
    <div class="ui-form__actions"><x-ui.button href="{{ route('admin.groups.show', $group) }}" variant="secondary">Отмена</x-ui.button><x-ui.button type="submit">Сохранить</x-ui.button></div>
</form></x-ui.card>
@endsection
