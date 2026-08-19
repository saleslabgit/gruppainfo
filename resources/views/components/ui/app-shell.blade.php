@props(['brand' => 'Gruppa Info', 'navigation' => null])
<div class="ui-shell">
    <aside class="ui-sidebar" aria-label="Основная навигация">
        <a class="ui-sidebar__brand" href="/">{{ $brand }}</a>
        <nav class="ui-sidebar__nav">{{ $navigation }}</nav>
    </aside>
    <header class="ui-topbar"><span class="ui-topbar__brand">{{ $brand }}</span><x-ui.icon-button label="Открыть меню" icon="menu" variant="filled" data-bs-toggle="offcanvas" data-bs-target="#app-drawer" aria-controls="app-drawer" /></header>
    <div class="offcanvas offcanvas-start ui-drawer" tabindex="-1" id="app-drawer" aria-labelledby="app-drawer-title">
        <header class="offcanvas-header"><strong id="app-drawer-title">{{ $brand }}</strong><x-ui.icon-button label="Закрыть меню" icon="x" data-bs-dismiss="offcanvas" /></header>
        <div class="offcanvas-body"><nav class="ui-sidebar__nav">{{ $navigation }}</nav></div>
    </div>
    <main class="ui-main">{{ $slot }}</main>
</div>
