@extends('layouts.admin')
@section('title', ($group->name ?: 'Группа').' — Gruppa Info')
@section('description', 'Карточка группы')
@section('admin-content')
<x-ui.page-header eyebrow="Группы" :title="$group->name ?: 'Без названия'" description="Содержание, модерация, публикация и история."><x-slot:actions><x-ui.button href="{{ route('admin.groups.edit', $group) }}" variant="secondary" icon="pencil">Редактировать</x-ui.button></x-slot:actions></x-ui.page-header>
<div class="ui-detail-stack">
    <x-ui.card variant="section" title="Состояние и владелец">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Внутренний ID">{{ $group->getKey() }}</x-ui.description-item><x-ui.description-item label="UUID">{{ $group->public_uuid }}</x-ui.description-item>
            <x-ui.description-item label="Психолог">{{ $group->owner->fullName() }} · {{ $group->owner->email }}</x-ui.description-item>
            <x-ui.description-item label="Статус"><x-ui.badge :variant="$group->status->badgeVariant()">{{ $group->status->label() }}</x-ui.badge></x-ui.description-item>
            <x-ui.description-item label="Тариф при создании">{{ $group->free ? 'Бесплатный' : 'Платный' }}</x-ui.description-item>
            <x-ui.description-item label="ID каталога">{{ $group->external_catalog_id ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Опубликована">@if($group->published_at)<x-ui.date :value="$group->published_at" />@else Не указано @endif</x-ui.description-item>
            <x-ui.description-item label="Завершение">@if($group->expires_at)<x-ui.date :value="$group->expires_at" />@else Не указано @endif</x-ui.description-item>
            <x-ui.description-item label="Срок размещения">{{ $group->placement_days ? $group->placement_days.' дн.' : 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Текущий комментарий модератора">{{ $group->moderator_comment ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Текущая причина отклонения">{{ $group->rejection_reason ?: 'Не указано' }}</x-ui.description-item>
        </x-ui.description-list>
        <div class="ui-detail-actions">
            @if($group->status === App\Domain\Group\GroupStatus::Moderation)
                <x-ui.button data-bs-toggle="modal" data-bs-target="#approve-group" icon="check">Одобрить</x-ui.button>
                <x-ui.button data-bs-toggle="modal" data-bs-target="#revision-group" variant="secondary" icon="undo-2">На доработку</x-ui.button>
                <x-ui.button data-bs-toggle="modal" data-bs-target="#reject-group" variant="danger" icon="x">Отклонить</x-ui.button>
            @elseif($group->status === App\Domain\Group\GroupStatus::Approved)
                <x-ui.button data-bs-toggle="modal" data-bs-target="#activate-group" icon="circle-check">Отметить активной</x-ui.button>
            @endif
            @can('cleanup', $group)<x-ui.button data-bs-toggle="modal" data-bs-target="#cleanup-group" variant="danger" icon="trash-2">Удалить черновик</x-ui.button>@endcan
        </div>
    </x-ui.card>
    <x-ui.card variant="section" title="Содержание">
        <x-ui.description-list :columns="2">
            <x-ui.description-item label="Название">{{ $group->name ?: 'Не указано' }}</x-ui.description-item><x-ui.description-item label="Формат">{{ $group->format?->name ?: 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Пол участников">{{ $group->gender?->name ?: 'Не указано' }}</x-ui.description-item><x-ui.description-item label="Длительность">{{ $group->meeting_duration_minutes ? $group->meeting_duration_minutes.' мин.' : 'Не указано' }}</x-ui.description-item>
            <x-ui.description-item label="Участников">{{ $group->participant_count ?? 'Не указано' }}</x-ui.description-item><x-ui.description-item label="Стоимость">@if($group->price_per_meeting !== null)<x-ui.money :minor-units="$group->price_per_meeting" />@else Не указано @endif</x-ui.description-item>
        </x-ui.description-list>
        <x-ui.description-list><x-ui.description-item label="Расписание">{{ $group->schedule ?: 'Не указано' }}</x-ui.description-item><x-ui.description-item label="Описание">{{ $group->description ?: 'Не указано' }}</x-ui.description-item></x-ui.description-list>
    </x-ui.card>
    @include('groups._history')
</div>
@if($group->status === App\Domain\Group\GroupStatus::Moderation)
<x-ui.modal id="approve-group" title="Одобрить группу" size="small"><p>Группа будет готова к ручной публикации.</p><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.groups.approve', $group) }}">@csrf<x-ui.button type="submit">Одобрить</x-ui.button></form></x-slot:footer></x-ui.modal>
<x-ui.modal id="revision-group" title="Отправить на доработку" size="small"><form id="revision-group-form" method="POST" action="{{ route('admin.groups.revision', $group) }}">@csrf</form><x-ui.form-field label="Комментарий" name="revision-comment" required :error="$errors->first('comment')"><x-ui.textarea id="revision-comment" name="comment" form="revision-group-form" required :error="$errors->has('comment')">{{ old('comment') }}</x-ui.textarea></x-ui.form-field><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><x-ui.button type="submit" form="revision-group-form">Отправить</x-ui.button></x-slot:footer></x-ui.modal>
<x-ui.modal id="reject-group" title="Отклонить группу" size="small"><form id="reject-group-form" method="POST" action="{{ route('admin.groups.reject', $group) }}">@csrf</form><x-ui.form-field label="Причина" name="reject-comment" required :error="$errors->first('comment')"><x-ui.textarea id="reject-comment" name="comment" form="reject-group-form" required :error="$errors->has('comment')">{{ old('comment') }}</x-ui.textarea></x-ui.form-field><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><x-ui.button type="submit" form="reject-group-form" variant="danger">Отклонить</x-ui.button></x-slot:footer></x-ui.modal>
@elseif($group->status === App\Domain\Group\GroupStatus::Approved)
<x-ui.modal id="activate-group" title="Отметить активной" size="small"><form id="activate-group-form" method="POST" action="{{ route('admin.groups.activate', $group) }}">@csrf</form><x-ui.form-field label="ID во внешнем каталоге" name="external_catalog_id" :error="$errors->first('external_catalog_id')"><x-ui.input name="external_catalog_id" form="activate-group-form" :value="old('external_catalog_id', $group->external_catalog_id)" :error="$errors->has('external_catalog_id')" /></x-ui.form-field><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><x-ui.button type="submit" form="activate-group-form">Активировать</x-ui.button></x-slot:footer></x-ui.modal>
@endif
@can('cleanup', $group)<x-ui.modal id="cleanup-group" title="Удалить черновик" size="small"><p>Будет выполнено мягкое удаление; связанные данные сохранятся.</p><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><form method="POST" action="{{ route('admin.groups.destroy', $group) }}">@csrf @method('DELETE')<x-ui.button type="submit" variant="danger">Удалить</x-ui.button></form></x-slot:footer></x-ui.modal>@endcan
@endsection
