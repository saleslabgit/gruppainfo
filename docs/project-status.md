# Статус проекта

Текущий реализованный этап: **Stage 7 — CRUD групп и модерация**. Финальная продуктовая/визуальная приёмка остаётся за пользователем.

## Работает сейчас

- стандартное Laravel 12 приложение на PHP 8.2.32;
- однокомандный запуск через Docker Compose;
- Nginx и MySQL 8.4.7 с healthchecks;
- отдельные MySQL-схемы для development и автоматических тестов;
- базовый Blade-layout и тестовая домашняя страница;
- локальный Bootstrap 5.3.8, `public/app.css` и `public/app.js` без build step;
- единое отображение UTC-дат в `Europe/Minsk`;
- единое форматирование целочисленных минорных денежных единиц с BYN по умолчанию;
- PHPUnit, Pint и Larastan/PHPStan уровня 5;
- Composer platform PHP 8.2.32 и явный список PHP-расширений;
- полная Stage 2 схема `gp_*`, database sessions, jobs и failed jobs;
- Eloquent-модели и связи пользователей, документов, групп, заявок, платежей, webhook, справочников и настроек;
- string-backed enum и исчерпывающие матрицы переходов для пользователей, групп и платежей;
- доменные сервисы переходов, включая транзакционную историю статусов групп;
- уникальность email активного пользователя через generated column;
- типизированный и кэшируемый сервис настроек;
- идемпотентный seed определений справочников, настроек, development/test-учётных записей и явно технических local/testing-элементов `group_format`/`gender`;
- database-драйверы сессий и очередей как проектные значения по умолчанию;
- токены цветов, типографики, spacing, controls, radii, focus, motion и elevation в `public/app.css`;
- локальные Montserrat 400–700 и Lucide 1.31.0 с лицензиями и без runtime CDN;
- общий namespace Blade-компонентов `resources/views/components/ui` для baseline actions, forms, feedback/data, navigation и overlays;
- responsive application shell с desktop sidebar и mobile drawer;
- data-driven Laravel pagination component;
- development/testing UI-kit на `/ui-kit`, недоступный при `APP_ENV=production`;
- браузерно проверенные desktop/mobile layout, custom select, modal, drawer, password input, pagination и keyboard focus;
- Stage 3 прошёл техническую и ручную визуальную приёмку.
- единый login для администратора и психолога на стандартном Laravel `web` guard;
- POST logout с инвалидацией сессии и ротацией CSRF token;
- защищённые `/admin` и `/cabinet` с взаимоисключающими role boundaries;
- повторная проверка `approved`/`disabled`/soft-delete на каждом защищённом запросе и немедленный отзыв доступа;
- переиспользуемая инвалидация всех database sessions одного пользователя;
- idempotent development/testing seed психолога вместе с существующим development-администратором;
- сохранённая Stage 4 auth/session boundary и административная landing page;
- responsive список психологов с поиском, комбинируемыми фильтрами, pagination и truthful empty states;
- ручное создание, detail и редактирование всех анкетных полей без password/email-onboarding;
- явные approve/reject, tariff и disable/enable действия через доменные границы;
- actor-aware `gp_user_action_history` для значимых административных действий;
- немедленная инвалидация database sessions при reject, disable и soft delete;
- policy-защищённые private document upload/view/download/delete с MIME-проверкой по содержимому;
- общие Chip, Filters/Drawer, Description List, File Upload и Document Item компоненты на `/ui-kit`.
- responsive кабинет психолога с общей desktop Sidebar/mobile Drawer навигацией;
- ownership-защищённые список, создание, просмотр, редактирование, отправка на модерацию и допустимое soft delete групп психолога;
- административные CRUD/list/search/filter/sort/pagination поверхности групп;
- state-machine модерация `draft → moderation → revision/rejected/approved → active` с полной actor-aware историей;
- ручная активация с UTC-датами, снимком срока размещения и необязательным ID внешнего каталога;
- общие Timeline и Money Input в production Blade namespace и на `/ui-kit`;
- Stage 7 free-path для любого тарифа без создания платежа и без достижимого `awaiting_payment`;
- read-only `/cabinet/profile`, который выбирает пользователя только из authenticated request и проходит отдельную self-view policy-проверку;
- собственные приватные документы психолога через существующие policy-защищённые view/download endpoints без раскрытия storage path.

## Намеренно не реализовано

Последующая продуктовая функциональность ещё не реализована. Пока нет управления справочниками/настройками, обработки заявок, scheduler-задач, публичного HTTP API, email-onboarding, SMTP-интеграции, платёжного адаптера, банковского webhook-контроллера и внешних интеграций.

Специализированные компоненты полного каталога (`Stepper`, `Choice Card`, `Metric`, `Progress`, tabs/breadcrumbs/popover/tooltip/toast/switch и другие пока не требуемые элементы) намеренно отложены. При первой продуктовой необходимости компонент сначала добавляется в общий UI namespace по `DESIGN_SYSTEM.md`, затем используется экраном; page-specific замены запрещены.

## Актуальный порядок работ

После Stage 3 проект развивается internal-first:

- Stage 4–10 — авторизация, внутренний CRUD, кабинеты, группы, lifecycle и работа с заявками без зависимости от публичного сайта, SMTP и банка;
- Stage 11 — входящие формы существующего сайта;
- Stage 12 — email, установка пароля и уведомления;
- Stage 13 — банковская интеграция размещения;
- Stage 14 — платное продление;
- Stage 15 — финальная Production-приёмка.

Это позволяет сначала стабилизировать CRUD, authorization, state transitions и пользовательские сценарии средствами кабинета, а внешние зависимости подключать позже по одной.

## Ограничения и открытые вопросы

- Production web server и production MySQL version ещё не выбраны; Docker-конфигурация предназначена только для разработки.
- Текущая домашняя страница является нейтральной технической проверкой, а не продуктовым интерфейсом.
- Production deployment automation относится к Stage 15.
- Production-значения элементов справочников и цены размещения/продления не утверждены и намеренно не заполняются production seed-ом; local/testing fixtures не являются продуктовыми значениями.
- Legacy-поле `accept` ещё не сопоставляется со статусами; оно не используется и не записывается доменной логикой.
- Платёжный провайдер и его протокол остаются неизвестными; провайдер-специфичная реализация блокируется до Stage 13 и получения официальных данных.
- Внешний integration secret и SMTP не нужны для ближайших внутренних CRUD-этапов.
- Числовой продуктовый лимит размера документа не утверждён; доступен необязательный `DOCUMENT_MAX_UPLOAD_KB`, а при пустом значении действует PHP/framework upload ceiling.
- Минимальная длина причины отклонения группы не определена; Stage 7 требует только непустую причину после trim.
- Возрастной порог «заброшенного» черновика не определён; Stage 7 оставляет обычный фильтр `draft` и ручное удаление без выдуманного порога.

## Следующий этап

Следующий этап по актуальному `SPEC.md` — **Stage 8: справочники, настройки и завершение административного CRUD**.
