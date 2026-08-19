# Gruppa Info

Отдельное Laravel-приложение для будущего кабинета психологов и административной панели. Сейчас реализован только технический фундамент Stage 1: Laravel, Docker-окружение, MySQL, базовая Blade-страница, локальные frontend-ресурсы, вспомогательные классы форматирования и инструменты качества.

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
