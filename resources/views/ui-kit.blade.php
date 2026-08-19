@extends('layouts.app')

@section('title', 'UI-kit — Gruppa Info')
@section('description', 'Локальная страница проверки дизайн-системы Gruppa Info')

@section('content')
@php
    $navigation = new Illuminate\Support\HtmlString('<div class="ui-nav-label">Система</div><a class="ui-nav-item is-active" href="#foundations"><i data-lucide="palette"></i>Основа</a><a class="ui-nav-item is-hovered" href="#forms"><i data-lucide="text-cursor-input"></i>Формы</a><a class="ui-nav-item" href="#data"><i data-lucide="table-2"></i>Данные</a><a class="ui-nav-item" href="#states"><i data-lucide="circle-alert"></i>Состояния</a>');
@endphp
<x-ui.app-shell :navigation="$navigation">
    <x-ui.page-header eyebrow="Stage 3" title="Базовая дизайн-система" description="Production-компоненты, токены и состояния для следующих этапов.">
        <x-slot:actions><x-ui.button icon="plus">Основное действие</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <section class="ui-kit-section" id="foundations">
        <h2>Основа и действия</h2><p class="ui-section-description">Типографика, основные цвета, кнопки и карточки.</p>
        <div class="ui-demo"><div class="ui-type-sample"><h1>Заголовок H1</h1><h2>Заголовок H2</h2><h3>Заголовок H3</h3><p>Основной текст интерфейса Montserrat.</p></div><div class="ui-swatches">@foreach(['primary','text','page','surface','success','warning','danger','info'] as $color)<span class="ui-swatch ui-swatch--{{ $color }}">{{ $color }}</span>@endforeach</div></div>
        <div class="ui-demo ui-demo--row"><x-ui.button icon="save">Основная</x-ui.button><x-ui.button variant="secondary">Вторичная</x-ui.button><x-ui.button variant="ghost">Ghost</x-ui.button><x-ui.button variant="danger">Удалить</x-ui.button><x-ui.button variant="text">Ссылка</x-ui.button><x-ui.button disabled>Disabled</x-ui.button><x-ui.button loading>Сохранение</x-ui.button><x-ui.icon-button label="Настройки" icon="settings" /></div>
        <div class="ui-demo ui-card-grid"><x-ui.card>Базовая карточка</x-ui.card><x-ui.card variant="interactive">Интерактивная карточка</x-ui.card><x-ui.card variant="highlighted">Выделенная карточка</x-ui.card><x-ui.card variant="section" title="С заголовком"><p>Содержимое секции.</p><x-slot:footer><x-ui.button size="small">Готово</x-ui.button></x-slot:footer></x-ui.card></div>
    </section>

    <section class="ui-kit-section" id="forms">
        <h2>Формы</h2><p class="ui-section-description">Единственный утверждённый размер полей и основные состояния.</p>
        <div class="ui-demo"><div class="ui-form-grid ui-form-grid--2"><x-ui.form-field label="Имя" name="name" required helper="Введите имя полностью"><x-ui.input name="name" value="Ирина" data-focus-example="input" /></x-ui.form-field><x-ui.form-field label="Email" name="email" error="Проверьте формат email"><x-ui.input name="email" type="email" value="wrong@" error /></x-ui.form-field><x-ui.form-field label="Только чтение" name="readonly" helper="Readonly сохраняет читаемый текст"><x-ui.input name="readonly" value="Заполняется автоматически" readonly /></x-ui.form-field><x-ui.form-field label="Пароль" name="password" helper="Переключатель доступен с клавиатуры"><x-ui.input name="password" type="password" value="secure-password" autocomplete="current-password" /></x-ui.form-field><x-ui.form-field label="Недоступное поле" name="disabled" disabled><x-ui.input name="disabled" value="Недоступно" disabled /></x-ui.form-field><x-ui.form-field label="Статус" name="status"><x-ui.select name="status" value="active" :options="['active' => 'Активна', 'draft' => 'Черновик', 'archive' => 'Архив']" /></x-ui.form-field></div><x-ui.form-field label="Комментарий" name="comment" helper="До 500 символов"><x-ui.textarea name="comment">Текст комментария</x-ui.textarea></x-ui.form-field><div class="ui-choice-group"><x-ui.checkbox name="terms" label="Согласие получено" checked /><x-ui.checkbox name="blocked" label="Недоступный вариант" disabled /><x-ui.radio name="format" value="online" label="Онлайн" checked /><x-ui.radio name="format" value="offline" label="Очно" /></div></div>
    </section>

    <section class="ui-kit-section" id="data">
        <h2>Обратная связь и данные</h2><p class="ui-section-description">Семантические состояния, администрирование списков и форматтеры.</p>
        <div class="ui-alert-stack"><x-ui.alert title="Информационное сообщение">Изменения сохраняются автоматически.</x-ui.alert><x-ui.alert variant="success" title="Успешно">Профиль верифицирован.</x-ui.alert><x-ui.alert variant="warning" title="Требуется внимание">Не все документы загружены.</x-ui.alert><x-ui.alert variant="danger" title="Ошибка">Не удалось сохранить изменения.</x-ui.alert></div>
        <div class="ui-demo ui-demo--row">@foreach(['neutral','info','success','warning','danger'] as $badge)<x-ui.badge :variant="$badge">{{ ucfirst($badge) }}</x-ui.badge>@endforeach</div>
        <div class="ui-table-wrap"><x-ui.table-toolbar><x-ui.search-input label="Поиск по имени" placeholder="Поиск по имени" data-focus-example="search" /><x-ui.button variant="secondary" size="small" icon="list-filter">Фильтр</x-ui.button><x-ui.badge variant="info">Активные</x-ui.badge><span class="ui-table-toolbar__count">124 результата</span><x-ui.button size="small" icon="plus">Добавить</x-ui.button></x-ui.table-toolbar><x-ui.table :headers="['', 'Имя', 'Статус', 'Сумма', 'Дата', '']"><x-ui.table-row><x-ui.checkbox name="row-1" label="" aria-label="Выбрать Ирину Ковалёву" /><strong>Ирина Ковалёва</strong><x-ui.badge variant="success">Активна</x-ui.badge><span class="ui-numeric"><x-ui.money :minor-units="12500" /></span><x-ui.date :value="now('UTC')" /><x-ui.dropdown><x-ui.dropdown-item icon="eye">Открыть</x-ui.dropdown-item><x-ui.dropdown-item icon="pencil">Изменить</x-ui.dropdown-item><div class="ui-dropdown__divider"></div><x-ui.dropdown-item icon="trash-2" danger>Удалить</x-ui.dropdown-item></x-ui.dropdown></x-ui.table-row><x-ui.table-row><x-ui.checkbox name="row-2" label="" aria-label="Выбрать Анну Орлову" /><strong>Анна Орлова</strong><x-ui.badge variant="warning">Проверка</x-ui.badge><span class="ui-numeric"><x-ui.money :minor-units="9900" /></span><x-ui.date :value="now('UTC')->subDay()" /><x-ui.icon-button label="Действия недоступны" icon="lock" disabled /></x-ui.table-row><x-slot:footer><x-ui.pagination :paginator="$firstPagePaginator" /></x-slot:footer></x-ui.table></div>
        <div class="ui-demo"><x-ui.pagination :paginator="$middlePagePaginator" /></div>
    </section>

    <section class="ui-kit-section" id="states">
        <h2>Состояния и overlays</h2><p class="ui-section-description">Пустые, загрузочные, ошибочные и подтверждающие состояния.</p>
        <div class="ui-demo ui-state-grid"><x-ui.empty-state title="Пока нет заявок" text="Новые заявки появятся здесь" /><div class="ui-state-panel"><x-ui.loading /><div class="ui-skeleton"><i></i><i></i><i></i></div></div><x-ui.error-state title="Не удалось загрузить" message="Повторите попытку позже"><x-ui.button variant="secondary" size="small">Повторить</x-ui.button></x-ui.error-state></div>
        <div class="ui-demo ui-demo--row"><x-ui.button data-bs-toggle="modal" data-bs-target="#sample-modal">Открыть modal</x-ui.button><x-ui.button variant="danger" data-bs-toggle="modal" data-bs-target="#confirm-modal">Подтверждение</x-ui.button><x-ui.error-state title="Поля формы заполнены с ошибками" inline /></div>
        <div class="ui-demo ui-confirmation-grid"><x-ui.confirmation title="Опубликовать изменения?" message="Новые данные сразу увидят пользователи после следующего обновления страницы." /><x-ui.confirmation title="Удалить запись?" message="Это действие нельзя отменить." danger /></div>
    </section>

    <section class="ui-kit-section" id="stage-five-components">
        <h2>Компоненты управления психологами</h2><p class="ui-section-description">Фильтры, chips, описание и приватные документы.</p>
        <div class="ui-demo ui-demo--row"><x-ui.chip>Обычный</x-ui.chip><x-ui.chip selected removable>Статус: принят</x-ui.chip><x-ui.chip disabled>Недоступен</x-ui.chip></div>
        <div class="ui-demo"><x-ui.filters id="sample-filters" action="/ui-kit" :active-count="1"><x-slot:fields><x-ui.form-field label="Статус" name="sample-status"><x-ui.select name="status" id="sample-status" value="approved" :options="['' => 'Все', 'approved' => 'Принят']" /></x-ui.form-field></x-slot:fields><x-slot:mobileFields><x-ui.form-field label="Статус" name="sample-status-mobile"><x-ui.select name="status" id="sample-status-mobile" value="approved" :options="['' => 'Все', 'approved' => 'Принят']" /></x-ui.form-field></x-slot:mobileFields></x-ui.filters></div>
        <div class="ui-demo"><x-ui.description-list :columns="2"><x-ui.description-item label="Имя">Анна Орлова</x-ui.description-item><x-ui.description-item label="Статус"><x-ui.badge variant="success">Принят</x-ui.badge></x-ui.description-item></x-ui.description-list></div>
        <div class="ui-demo ui-form-stack"><x-ui.file-upload name="sample-document" label="Выберите или перетащите файл" /><div class="ui-document-list"><x-ui.document-item name="Диплом.pdf" meta="Диплом · 1,2 МБ" view-href="#" download-href="#" /></div></div>
    </section>

    <x-ui.modal id="sample-modal" title="Информационное окно"><p>Modal использует общий production-компонент и локальный Bootstrap bundle.</p><x-slot:footer><x-ui.button data-bs-dismiss="modal">Понятно</x-ui.button></x-slot:footer></x-ui.modal>
    <x-ui.modal id="confirm-modal" title="Удалить запись?" size="small"><p>Это действие нельзя отменить.</p><x-slot:footer><x-ui.button variant="secondary" data-bs-dismiss="modal">Отмена</x-ui.button><x-ui.button variant="danger">Удалить</x-ui.button></x-slot:footer></x-ui.modal>
</x-ui.app-shell>
@endsection
