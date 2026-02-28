# Серверная часть информационной системы складского учёта ООО «Айрус»

Курсовой проект по специальности 09.02.07 «Информационные системы и программирование».

**Репозиторий:** [GitHub](https://github.com/)

## Описание

Веб-приложение для автоматизации складского учёта: номенклатура товаров, учёт операций (производство, закупка, продажа), расчёт остатков, отчётность, разграничение доступа по ролям, логирование действий.

## Технологии

- **Laravel** 13.x
- **PHP** 8.3+
- **MySQL** 8.0 или **SQLite** (для разработки)
- **Bootstrap 5**
- **Chart.js** (аналитика)
- **PhpSpreadsheet** (экспорт XLSX)

## Установка

### 1. Клонирование и зависимости

```bash
git clone https://github.com/YOUR_USERNAME/laravel-warehouse-accounting.git airus
cd airus
composer install
```

### 2. Настройка окружения

```bash
cp .env.example .env
php artisan key:generate
```

Отредактируйте `.env` — укажите параметры БД:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=airus
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Миграции и сиды

```bash
php artisan migrate --force
php artisan db:seed
```

### 4. Запуск

```bash
php artisan serve
```

Приложение будет доступно по адресу: http://localhost:8000

## Тестовые учётные записи

| Роль      | Логин     | Пароль       |
|-----------|-----------|--------------|
| Менеджер  | manager   | manager123   |
| Бухгалтер | accountant| accountant123|
| Директор  | director  | director123  |

## Структура ролей

- **Менеджер** — товары (CRUD), операции, отчёты, экспорт JSON/TXT/XLSX
- **Бухгалтер** — просмотр отчётов, зарплатная ведомость, экспорт TXT
- **Директор** — просмотр товаров, операций, отчётов, аналитика, журнал действий, экспорт XLSX

## Тестирование

Создайте тестовую БД (если используется MySQL):
```sql
CREATE DATABASE airus_test;
```

```bash
php artisan test
```

> Если SQLite расширение включено в PHP, тесты используют in-memory SQLite. Иначе нужна MySQL с базой `airus_test`.

## Маршруты

- `GET /login` — форма входа
- `POST /login` — аутентификация
- `POST /logout` — выход
- `GET /dashboard` — главная
- `GET/POST /products` — товары
- `GET/POST /operations` — операции
- `GET /operations/all` — полный журнал операций
- `GET /reports` — отчёты
- `GET /salary` — зарплатная ведомость
- `GET /analytics` — аналитика (директор)
- `GET /logs` — журнал действий (директор)

## Публикация на GitHub

Локальный репозиторий уже инициализирован. Для загрузки на GitHub:

1. Создайте новый репозиторий на [github.com](https://github.com/new):
   - Название: `laravel-warehouse-accounting` (или любое другое)
   - Публичный (Public)
   - **Не** добавляйте README, .gitignore или license — они уже есть в проекте

2. Выполните команды (подставьте свой username и название репо):

```bash
git remote add origin https://github.com/YOUR_USERNAME/laravel-warehouse-accounting.git
git branch -M main
git push -u origin main
```

## Лицензия

MIT
