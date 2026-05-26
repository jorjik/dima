# Деплой Laravel проекта: Альтернативы Vercel

## Почему не Vercel?

Vercel оптимизирован для Node.js/Next.js и статических сайтов. Для Laravel (PHP) с базой данных, файловым хранилищем и Filament админ-панелью нужна платформа с полноценным серверным окружением.

---

## 🏆 Рекомендуемые платформы для Laravel

### 1. **Railway** (Уже настроен! ✅)

**Преимущества:**

- ✅ У вас уже есть Dockerfile и конфигурация
- ✅ Автоматический деплой из Git
- ✅ Встроенная PostgreSQL/MySQL
- ✅ Persistent storage для файлов
- ✅ $5/месяц бесплатно

**Что нужно сделать:**

```bash
# 1. Установить Railway CLI
npm i -g @railway/cli

# 2. Войти в аккаунт
railway login

# 3. Инициализировать проект
cd c:/server/www/dima
railway init

# 4. Добавить PostgreSQL
railway add

# 5. Настроить переменные окружения
railway variables set APP_KEY=$(php artisan key:generate --show)
railway variables set APP_URL=https://your-app.up.railway.app

# 6. Деплой
railway up
```

**Конфигурация уже готова:**

- ✅ `Dockerfile` - есть
- ✅ `RAILWAY.md` - инструкции есть
- ✅ Volume для storage - описан

---

### 2. **Fly.io** (Рекомендуется)

**Преимущества:**

- 🚀 Очень быстрый (edge locations по всему миру)
- 💰 Бесплатный tier: 3 VM + 3GB storage
- 🐳 Поддержка Docker
- 📦 Автоматическое масштабирование

**Инструкция:**

```bash
# 1. Установить Fly CLI
powershell -Command "iwr https://fly.io/install.ps1 -useb | iex"

# 2. Войти
fly auth login

# 3. Запустить проект
cd c:/server/www/dima
fly launch

# 4. Добавить PostgreSQL
fly postgres create

# 5. Подключить БД
fly postgres attach <postgres-app-name>

# 6. Настроить volume для storage
fly volumes create storage_data --size 1

# 7. Деплой
fly deploy
```

**Создать `fly.toml`:**

```toml
app = "dima-laravel"
primary_region = "fra"

[build]
  dockerfile = "Dockerfile"

[env]
  APP_ENV = "production"
  LOG_CHANNEL = "stderr"
  LOG_LEVEL = "info"
  LOG_STDERR_FORMATTER = "Monolog\\Formatter\\JsonFormatter"

[http_service]
  internal_port = 8000
  force_https = true
  auto_stop_machines = true
  auto_start_machines = true
  min_machines_running = 0

[[mounts]]
  source = "storage_data"
  destination = "/var/www/html/storage"

[[vm]]
  cpu_kind = "shared"
  cpus = 1
  memory_mb = 256
```

---

### 3. **Render** (Простой в настройке)

**Преимущества:**

- 🎯 Простой UI
- 💰 Бесплатный tier
- 🔄 Автодеплой из GitHub
- 📊 Встроенный мониторинг

**Инструкция:**

1. Зарегистрируйтесь на [render.com](https://render.com)

2. Подключите GitHub репозиторий

3. Создайте **Web Service**:
   
   - **Environment**: Docker
   - **Dockerfile Path**: `./Dockerfile`
   - **Plan**: Free

4. Добавьте **PostgreSQL**:
   
   - New → PostgreSQL
   - Скопируйте `DATABASE_URL`

5. Настройте **Environment Variables**:
   
   ```
   APP_KEY=base64:...
   APP_URL=https://your-app.onrender.com
   DATABASE_URL=postgresql://...
   ```

6. Добавьте **Disk** для storage:
   
   - Settings → Disks → Add Disk
   - Mount Path: `/var/www/html/storage`
   - Size: 1GB

---

### 4. **DigitalOcean App Platform**

**Преимущества:**

- 🏢 Надежная инфраструктура
- 💰 $5/месяц
- 🔧 Managed PostgreSQL
- 📦 Автоматические бэкапы

**Инструкция:**

1. Зарегистрируйтесь на [digitalocean.com](https://www.digitalocean.com)

2. Apps → Create App

3. Выберите GitHub репозиторий

4. Настройте:
   
   - **Type**: Web Service
   - **Dockerfile**: Автоопределится
   - **HTTP Port**: 8000

5. Добавьте PostgreSQL:
   
   - Add Resource → Database
   - PostgreSQL 15

6. Environment Variables:
   
   ```
   APP_KEY=${APP_KEY}
   DATABASE_URL=${db.DATABASE_URL}
   ```

---

### 5. **Laravel Forge + Любой VPS** (Профессиональное решение)

**Преимущества:**

- 🎯 Специально для Laravel
- 🔧 Автоматическая настройка сервера
- 🚀 Zero-downtime deployments
- 📊 Мониторинг и логи

**Стоимость:**

- Forge: $12/месяц
- VPS (DigitalOcean/Linode): от $6/месяц

**Инструкция:**

1. Зарегистрируйтесь на [forge.laravel.com](https://forge.laravel.com)
2. Подключите VPS провайдера (DigitalOcean/Linode/Vultr)
3. Создайте сервер через Forge
4. Добавьте сайт:
   - Repository: ваш GitHub
   - Branch: main
5. Настройте деплой скрипт:
   
   ```bash
   cd /home/forge/your-site
   git pull origin main
   composer install --no-dev --optimize-autoloader
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan storage:link
   ```

---

## 📊 Сравнительная таблица

| Платформа        | Бесплатный tier | Сложность | Laravel support | Рекомендация    |
| ---------------- | --------------- | --------- | --------------- | --------------- |
| **Railway**      | $5 кредит       | ⭐⭐        | ✅ Отлично       | ✅ Уже настроен  |
| **Fly.io**       | 3 VM бесплатно  | ⭐⭐⭐       | ✅ Отлично       | ✅ Лучший выбор  |
| **Render**       | Есть            | ⭐         | ✅ Хорошо        | ✅ Самый простой |
| **DigitalOcean** | Нет ($5/мес)    | ⭐⭐        | ✅ Отлично       | ✅ Надежный      |
| **Forge + VPS**  | Нет ($18/мес)   | ⭐⭐⭐⭐      | ✅ Идеально      | ✅ Production    |

---

## 💡 Что такое "Бесплатный tier"?

**Tier** (англ. "уровень", "ярус") — это тарифный план сервиса.

**Бесплатный tier** — это бесплатная версия хостинга с ограничениями.

### Что входит в бесплатные планы:

**Railway:**

- 💵 $5 бесплатных кредитов каждый месяц
- ⚡ Достаточно для небольшого проекта
- 🔄 Кредиты обновляются ежемесячно

**Fly.io:**

- 🖥️ 3 виртуальные машины бесплатно (я не нашёл!)
- 💾 3GB хранилища бесплатно
- 🌍 Глобальная CDN
- ✅ Отлично для тестовых проектов

**Render:**

- 🆓 Полностью бесплатный план
- ⚠️ Проект "засыпает" после 15 минут неактивности
- 🐌 "Холодный старт" при первом запросе (может занять 30-60 секунд)
- 📊 Подходит для демо и портфолио

### Ограничения бесплатных планов:

- 🔻 Меньше ресурсов (память, CPU)
- ⏱️ Возможны задержки при запуске
- 📉 Ограничения по трафику
- ❌ Нет гарантий uptime (99.9%)
- 💤 Проект может останавливаться при неактивности
- 🚫 Нет автоматических бэкапов
- 📧 Ограниченная поддержка

### Когда нужен платный tier:

✅ **Продакшн-проект** с реальными пользователями  
✅ Нужна **стабильная работа 24/7**  
✅ **Большой трафик** (>10,000 посещений/месяц)  
✅ Нужны **автоматические бэкапы**  
✅ Требуется **техподдержка**  
✅ **Коммерческий проект** с оплатой

### Рекомендации для вашего проекта:

**Для разработки и тестирования:**

- 🎯 **Railway** (уже настроен) — $5/месяц хватит на старт
- 🎯 **Fly.io** — 3 VM достаточно для тестов

**Для продакшна:**

- 💰 **Railway** — $5-20/месяц (в зависимости от нагрузки)
- 💰 **DigitalOcean** — от $5/месяц (стабильно)
- 💰 **Forge + VPS** — от $18/месяц (профессионально)

---

## 🎯 Моя рекомендация

### Для вашего проекта:

**1. Railway** (быстрый старт)

- У вас уже всё настроено
- Просто запустите `railway up`
- Работает из коробки

**2. Fly.io** (если нужна скорость)

- Создайте `fly.toml` (я могу помочь)
- Запустите `fly launch`
- Глобальная CDN

---
