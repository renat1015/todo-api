<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">TODO LIST API</h1>
</p>

Простое RESTful API для управления списком задач на базе Yii2.

СТРУКТУРА ПРОЕКТА
-------------------

      config/          # Конфигурация приложения
      controllers/     # Контроллеры
      docker/          # Docker конфигурация
      migrations/      # Миграции БД
      models/          # Модели
      modules/         # Модули
      services/        # Сервисы
      web/             # Веб-корень

ТРЕБОВАНИЯ
-------------------
- Docker и Docker Compose
- Composer (Локальная разработка)

УСТАНОВКА И ЗАПУСК
-------------------
### Используя Docker

1. Склонируйте репозиторий:
```
git clone https://github.com/renat1015/todo-api

cd todo-api
```
2.  Скопируйте файл окружения:
```
cp .env.example .env
```
3.  Запустите контейнеры:
```
docker-compose up -d
```
4.  Выполните инициализацию:
```
docker-compose exec php-fpm bash docker/scripts/init.sh
```
5.  Приложение будет доступно по адресу:

      http://localhost:8080

### Без Docker (Локальная разработка)

1.  Установите зависимости:
```
composer install
```
2.  Настройте базу данных в файле config/db.php
    
3.  Примените миграции:
```
php yii migrate
```
4.  Запустите встроенный сервер:
```
php yii serve
```

КОНФИГУРАЦИЯ
-------------

### Database

Настройте файл `config/db.php` с вашими данными, по примеру:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=todo_db',
    'username' => 'todo_user',
    'password' => 'todo_password',
    'charset' => 'utf8mb4',
];
```

API Endpoints
-------------
- GET /api/task - Получение списка всех задач
- GET /api/task/`id` - Получение информации о конкретной задаче
- POST /api/task - Создание новой задачи
- PUT /api/task/`id` - Обновление задачи
- DELETE /api/task/`id` - Удаление задачи
