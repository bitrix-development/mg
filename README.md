# 📋 Описание проекта LK (Личный кабинет руководителя отдела продаж)

## 1. Структура БД и таблиц

### users
- id (int, PK, AI)
- login (varchar, уникально)
- password (varchar, хеш)
- name (varchar)
- email (varchar)
- role (enum: manager, admin, sales)
- created_at (timestamp)
- reset_token (varchar)
- reset_token_expires (timestamp)

### clients
- id (int, PK, AI)
- name (varchar)
- email (varchar)
- phone (varchar)
- address (text)
- status (enum: active, inactive)
- created_at (timestamp)

### orders
- id (int, PK, AI)
- client_id (int, FK)
- user_id (int, FK)
- order_date (timestamp)
- status (enum: new, processing, completed, cancelled)
- total (decimal)

### order_items
- id (int, PK, AI)
- order_id (int, FK)
- product_name (varchar)
- quantity (int)
- price (decimal)
- total_price (decimal)

### products
- id (int, PK, AI)
- product_name (varchar)
- size (varchar)
- price (decimal)

### login_attempts
- id (int, PK, AI)
- login (varchar)
- ip_address (varchar)
- attempt_time (timestamp)
- was_successful (bool)

### user_logs
- id (int, PK, AI)
- user_id (int, FK nullable)
- action (varchar)
- description (text)
- ip_address (varchar)
- created_at (timestamp)

### rate_limit_sessions
- id (int, PK, AI)
- identifier (varchar, уникальный)
- login (varchar)
- ip_address (varchar)
- attempt_count (int)
- first_attempt (timestamp)
- last_attempt (timestamp)
- is_blocked (bool)
- blocked_until (timestamp)

---

## 2. Основные файлы и структура

- /db.php — подключение к БД (будет на .env)
- /config.php — параметры (устаревшие, уйдут в .env)
- /.env — переменные окружения
- /functions.php — вспомогательные функции (будет логирование, работа с лимитом)
- /includes/security.php — проверки авторизации, CSRF-защита
- /includes/Logger.php — логирование действий, попыток входа (есть на диске)
- /includes/RateLimiter.php — класс для ограничения частоты входа
- /admin_change_password.php — смена пароля админом
- /phpinfo.php — информация по PHP

#### pages/
- dashboard.php — дашборд-обзор
- clients.php — список клиентов
- client_detail.php — детали клиента и заказы
- orders.php — список заказов, с фильтрами по статусу
- order_detail.php — детали заказа (будет)
- profile.php — профиль пользователя, смена пароля
- login.php — форма входа (будет интеграция rate limit и логирование + CSRF)
- logout.php — выход из системы (работает правильно)
- activity_log.php — архив действий (будет)

#### includes/
- header.php, footer.php — оформление

---

## 3. Главное, что уже реализовано
- Система авторизации через login/password
- Роли пользователя (manager, admin, sales)
- Список клиентов с фильтрацией
- Список заказов (фильтры — реализованы частично)
- Защита от CSRF, XSS
- Сброс пароля вручную админом
- Раздел профиля и смена пароля
- Архитектура базы полностью готова для бизнеса
- Выход пользователя (logout.php)
- Классы для логирования и ограничения частоты — на диске, внедрение впереди

---

## 4. План работ (следующие этапы)
1. Внедрение использования .env для db.php (скрытие паролей)
2. Перевод /config.php на .env (удалить личные данные из кода)
3. Интеграция RateLimiter и логирования в login.php (и в ключевые действия)
4. Оформление /activity_log.php — страница для просмотра всей истории действий
5. Миграция старого кода на новые функции log/rateLimit
6. Добавление сброса пароля через email (reset_token, формы)
7. Создание полноценной страницы order_detail.php
8. Протестировать все фильтры заказов, поиск, права
9. Добавить админ-архив логов (чистка старых записей)

---

## 5. Важные проблемы, требующие внимания
- DB-пароль и прочие параметры до сих пор в config.php → СРОЧНО в .env, коммитить config.php только без секретов!
- RateLimiter и Logger не интегрированы в живой логин/действия. Реальные попытки и логи не ведутся — СРОЧНО внедрять класс!
- Нет реализации восстановления пароля через e-mail без участия админа
- Нет АРМ для просмотра логов (admin-панель)
- Нет UX-нагрузочных/автоматизированных тестов (риск багов в проде)

---

## 6. РЕЗЮМЕ
- Проект LK — это личный кабинет, предназначенный для руководителя и персонала отдела продаж.
- Система построена с акцентом на безопасность и прозрачность действий пользователей (роль, авторизация, логирование).
- Вместе с core-функциями, codebase уже готов к внедрению бизнес-логики без опасных «дырок», когда будут завершены задачи по rate limit, шифрованию паролей БД, ведению лога.
- Недостающие модули — восстановление пароля по email, ARМ по логам, глубинные фильтры по заказам. Все это планируется реализовать в текущий или ближайший релизный цикл.

---

**Актуально на:** 2026-02-16 (заполнение autogen)

---
