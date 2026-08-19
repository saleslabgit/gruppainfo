@extends('layouts.admin')

@section('title', 'Редактирование психолога — Gruppa Info')
@section('description', 'Редактирование данных психолога')

@section('admin-content')
<x-ui.page-header eyebrow="Психологи" title="Редактирование" description="Изменение анкетных данных {{ $psychologist->fullName() }}." />
<x-ui.card variant="section" title="Данные психолога">
    <form class="ui-form" method="POST" action="{{ route('admin.psychologists.update', $psychologist) }}">
        @csrf
        @method('PUT')
        @include('admin.psychologists._form')
        <div class="ui-form__actions"><x-ui.button href="{{ route('admin.psychologists.show', $psychologist) }}" variant="secondary">Отмена</x-ui.button><x-ui.button type="submit">Сохранить</x-ui.button></div>
    </form>
</x-ui.card>
@endsection
