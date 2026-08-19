@extends('layouts.cabinet')
@section('title', 'Редактирование группы — Gruppa Info')
@section('description', 'Данные группы')
@section('cabinet-content')
<x-ui.page-header eyebrow="Мои группы" :title="$group->name ?: 'Новая группа'" description="Черновик можно сохранять незаполненным. Перед модерацией потребуются все поля." />
<x-ui.card>
    <form class="ui-form" method="POST" action="{{ route('cabinet.groups.update', $group) }}">@csrf @method('PUT')
        @include('groups._form')
        <div class="ui-form__actions"><x-ui.button href="{{ route('cabinet.groups.show', $group) }}" variant="secondary">Отмена</x-ui.button><x-ui.button type="submit">Сохранить</x-ui.button></div>
    </form>
</x-ui.card>
@endsection
