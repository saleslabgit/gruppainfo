# Gruppa Info

Отдельное Laravel-приложение для будущего кабинета психологов и административной панели. Реализованы технический фундамент и Stage 2: MySQL-схема предметной области, Eloquent-модели, статусные машины, история статусов групп, типизированные настройки и начальные seed-данные.

## Технический стек

- Laravel 12.x;
- PHP 8.2.32;
- MySQL 8.4.7;
- Nginx 1.28.0;
- Blade и локальный Bootstrap 5.3.8;
- Composer, Pint, Larastan/PHPStan и PHPUnit.

Node.js, npm, Vite и сборка frontend-ресурсов не используются.

## Запуск

Нужен только Docker с Docker Compose. Из корня чистого checkout выполните:

```bash
docker compose up --build -d
```

Команда собирает PHP-образ, запускает MySQL и Nginx, создаёт локальный `.env`, устанавливает Composer-зависимости, генерирует `APP_KEY` и выполняет миграции. После успешного запуска приложение доступно по адресу [http://localhost:8080](http://localhost:8080).

Для создания определений справочников, настроек и локального администратора выполните:

```bash
docker compose exec app php artisan db:seed
```

Данные development-администратора задаются переменными `SEED_ADMIN_*` из `.env`. Указанные в `.env.example` значения предназначены только для локальной разработки и тестов.

## Основные команды

```bash
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint --test
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
docker compose exec app composer check-platform-reqs
docker compose down
```

Подробности:

- [Архитектура](docs/architecture.md)
- [Разработка](docs/development.md)
- [Статус проекта](docs/project-status.md)
