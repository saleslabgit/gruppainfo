# Разработка

## Окружение

Единственная обязательная host-зависимость — Docker с Docker Compose. Compose запускает:

| Сервис | Версия | Назначение |
|---|---:|---|
| `app` | PHP 8.2.32 FPM | Laravel, Composer и CLI-команды |
| `web` | Nginx 1.28.0 Alpine | HTTP на `localhost:8080` |
| `db` | MySQL 8.4.7 | development- и test-схемы |

Исходный код подключается в контейнер как обычные файлы репозитория. Composer-зависимости, Composer cache и MySQL-данные хранятся в именованных Docker volumes и не коммитятся.

## Первый и последующие запуски

```bash
docker compose up --build -d
```

При первом запуске entrypoint:

1. копирует `.env.example` в игнорируемый `.env`, если файла ещё нет;
2. выполняет `composer install`;
3. генерирует локальный `APP_KEY`, если он отсутствует;
4. готовит writable-каталоги Laravel;
5. выполняет `php artisan migrate --force`;
6. запускает PHP-FPM.

MySQL имеет healthcheck; `app` запускается только после готовности базы, а Nginx — после готовности PHP-FPM.

После первого запуска создайте начальные справочники, настройки и development-учётные записи администратора и психолога:

```bash
docker compose exec app php artisan db:seed
```

Seed идемпотентен. Он не создаёт элементы справочников и не задаёт цены размещения/продления: эти значения пока не утверждены.

Проверить состояние и остановить окружение:

```bash
docker compose ps
docker compose down
```

## Переменные окружения

Локальные безопасные значения находятся в `.env.example`. Development-схема — `gruppainfo`, test-схема — `gruppainfo_test`. Обе создаются при первом запуске MySQL, а пользователь `gruppainfo` получает доступ к обеим. Сессии и очереди по умолчанию используют database-драйверы; их таблицы создаются миграциями.

Локальные seed-учётные записи настраиваются через `SEED_ADMIN_EMAIL`, `SEED_ADMIN_PASSWORD`, `SEED_ADMIN_FIRST_NAME` и аналогичные `SEED_PSYCHOLOGIST_EMAIL`, `SEED_PSYCHOLOGIST_PASSWORD`, `SEED_PSYCHOLOGIST_FIRST_NAME`. Значения из `.env.example` предназначены только для development/testing. При `APP_ENV=production` эти учётные записи не создаются.

После `php artisan db:seed` вход доступен по `http://localhost:8080/login`:

- администратор: `admin@example.test` / `local-development-only`, после входа `/admin`;
- психолог: `psychologist@example.test` / `local-development-only`, после входа `/cabinet`.

Это локальные значения по умолчанию из `.env.example`; при env-переопределении используйте настроенные значения. Logout выполняется только POST-формой внутри защищённой области. Порог login rate limit при необходимости настраивается через `AUTH_LOGIN_MAX_ATTEMPTS` и `AUTH_LOGIN_DECAY_SECONDS` (по умолчанию 5 попыток и 60 секунд).

Приватные документы психологов хранятся на disk `local` в `storage/app/private`. Необязательный `DOCUMENT_MAX_UPLOAD_KB` задаёт максимальный размер одного файла в килобайтах. Числовое продуктовое значение пока не утверждено, поэтому переменная по умолчанию пуста и Laravel/PHP применяют системный upload ceiling; allowlist PDF/JPEG/PNG и проверка фактического MIME действуют независимо от этого значения.

`phpunit.xml` принудительно задаёт test-схему, поэтому запущенный в контейнере `php artisan test` не использует development-схему и не использует SQLite.

Для production создайте отдельный `.env`, используйте реальные секреты, задайте `APP_ENV=production`, `APP_DEBUG=false`, production URL и production MySQL-реквизиты. Docker Compose и его локальные пароли не предназначены для production.

Production-обработчик database-очереди должен запускаться под process manager либо периодически командой:

```bash
php artisan queue:work --stop-when-empty
```

## Composer

Composer доступен внутри `app`; host-установка не нужна:

```bash
docker compose exec app composer install
docker compose exec app composer update
docker compose exec app composer check-platform-reqs
```

`composer.json` эмулирует production PHP 8.2.32 через `config.platform.php`. Требуемые расширения: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `phar`, `session`, `tokenizer`, `xml`, `xmlwriter`.

## Проверки

Полный текущий тестовый набор на MySQL:

```bash
docker compose exec app php artisan test
```

Для ручной проверки создания базы с нуля используйте только одноразовую/test-схему. Следующая команда полностью удаляет данные `gruppainfo_test` и никогда не должна направляться на development или production:

```bash
docker compose exec -e APP_ENV=testing -e DB_DATABASE=gruppainfo_test app php artisan migrate:fresh --seed --force
```

Форматирование в check-режиме:

```bash
docker compose exec app ./vendor/bin/pint --test
```

Larastan/PHPStan использует `phpstan.neon` и фиксированный уровень 5:

```bash
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
```

Проверка PHP-платформы:

```bash
docker compose exec app composer check-platform-reqs
```

Composer-скрипт `composer check` последовательно запускает Pint, Larastan и тесты.

## Frontend-ресурсы и UI-kit

Bootstrap 5.3.8 хранится в `public/vendor/bootstrap` вместе с лицензией. Montserrat 9.000 взят из официального репозитория Google Fonts как `Montserrat-VariableFont_wght.ttf`; локальный файл обслуживает веса 400, 500, 600 и 700, лицензия OFL лежит рядом. Закрепляющая SHA-256 сумма font-файла: `0f7b311b2f3279e4eef9b2f968bcdbab6e28f4daeb1f049f4f278a902bcd82f7`. Lucide 1.31.0 взят из официального npm-distribution как browser-ready UMD-файл; ISC-лицензия также лежит рядом, SHA-256 distribution-файла: `f96167bbf0e73ae1031328116cc36ba633c71953d0ccce2e4b5cfc17c420f869`.

Собственные файлы — `public/app.css` и `public/app.js`. Они подключаются напрямую из Blade; `app.css` подключается после Bootstrap. Frontend-сборки и runtime CDN-запросов нет.

В окружениях `local` и `testing` baseline проверяется на `/ui-kit`. При `APP_ENV=production` маршрут возвращает 404. Страница использует production-компоненты из `resources/views/components/ui` и предназначена для проверки desktop/mobile layout, focus и интерактивных состояний. Минимальная ручная проверка:

1. открыть `http://localhost:8080/ui-kit` на desktop и при ширине 390px;
2. проверить sidebar/mobile drawer, custom select, dropdown и modal;
3. пройти интерактивные элементы клавишей Tab и проверить видимый focus;
4. убедиться через Network, что Bootstrap, Montserrat, Lucide и project assets загружаются только с текущего host.

Новые runtime CSS/JS-файлы также должны размещаться непосредственно в `public/`. Не добавляйте `package.json`, npm, Vite или CDN-ссылки.

## Ручная проверка Stage 5

После миграций и seed войдите development-администратором и проверьте сценарий:

1. открыть `/admin/psychologists`, выполнить поиск и совместить фильтры статуса, тарифа и доступа;
2. создать психолога, открыть detail и изменить анкетные данные;
3. на отдельных pending-записях выполнить approve и reject, затем проверить смену тарифа и disable/enable;
4. загрузить PDF/JPEG/PNG, открыть и скачать его только через controller URL, затем удалить;
5. soft-delete тестового психолога и убедиться, что он исчез из списка;
6. повторить list/form/detail/filter/confirmation/document flow при desktop и mobile ширине, проверить console и Network на ошибки и внешние/CDN-ресурсы.

Сфокусированные проверки Stage 5:

```bash
docker compose exec app php artisan test tests/Feature/AdminPsychologistCrudTest.php tests/Feature/PsychologistDocumentTest.php tests/Feature/UiKitPageTest.php
```

## Проверенная диагностика

Если страница не открывается, сначала проверьте health/status и логи:

```bash
docker compose ps
docker compose logs app db web
```

При изменении `Dockerfile` повторно выполните обычную команду запуска с `--build`.
