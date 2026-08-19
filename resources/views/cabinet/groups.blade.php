@extends('layouts.cabinet')

@section('title', 'Мои группы — Gruppa Info')
@section('description', 'Группы психолога')

@section('cabinet-content')
<x-ui.page-header eyebrow="Кабинет психолога" title="Мои группы" description="Здесь будут отображаться ваши группы после их добавления." />

<x-ui.empty-state icon="users-round" title="Пока нет групп" text="Добавленные группы появятся в этом разделе." />
@endsection
