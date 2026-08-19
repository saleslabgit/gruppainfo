@extends('layouts.admin')

@section('title', 'Администрирование — Gruppa Info')
@section('description', 'Защищённая административная область Gruppa Info')

@section('admin-content')
<x-ui.page-header eyebrow="Администратор" title="Административная область" description="Управление внутренними данными Gruppa Info." />
<x-ui.card variant="section" title="Психологи">
    <p>Создание, проверка и управление анкетами и приватными документами психологов.</p>
    <p>Роль: администратор</p>
    <x-ui.button href="{{ route('admin.psychologists.index') }}" icon="users">Открыть список психологов</x-ui.button>
</x-ui.card>
@endsection
