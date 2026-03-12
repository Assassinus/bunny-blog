# Bunny Blog

Небольшой блог о кроликах на чистом PHP: главная с категориями, страница категории с пагинацией и сортировкой, страница статьи и похожие статьи.

**Стек:**
- PHP 8.3  
- Smarty 5  
- MySQL 8  
- Docker (nginx + PHP-FPM + MySQL)

**Архитектура:** Front controller → Router → Controller → Service → Repository → Database.
Доменные сущности представлены immutable-объектами (`readonly`). Роуты в конфиге, DI-контейнер собирает граф, контроллеры тонкие - только вызов сервисов и рендер.

**Безопасность:** 
- Prepared statements для всех SQL-запросов
- Auto-escape в шаблонах Smarty
- Заголовки безопасности:
    - Content Security Policy
    - X-Frame-Options
    - X-Content-Type-Options
- Сессии с `HttpOnly` и `SameSite`
- Валидация slug из URL перед запросами к БД

**Запуск:**
```bash
cp .env.example .env && composer install
mkdir -p var/smarty/compile var/smarty/cache var/cache var/log && chmod -R 777 var
docker compose up --build -d
docker compose exec app php /var/www/html/database/seed.php
```
Фронт: http://localhost:8080  
Проверка health: http://localhost:8080/health

**Использование ИИ:**
ИИ использовался для фронта (темлпейтов Smarty, стилей), генерации примеров тестов, наполнения статей в сидере, скачивания изображений

