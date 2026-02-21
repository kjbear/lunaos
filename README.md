# LunaOS

A unified dashboard platform for AI assistant team visibility.

## About

LunaOS provides real-time visibility into Luna and her sub-agent team's activities, tasks, and decisions.

**Phase 1 Modules:**
- Task Manager — Real-time task tracking
- Org Chart — Team hierarchy and model health
- Activity Feed — Complete audit log
- Calendar — Weekly view of scheduled tasks
- Global Search — Full-text search across memory, docs, tasks
- Standup Recording — Team standups with transcripts

## Tech Stack

- **Laravel 12** — PHP framework
- **Livewire 3** — Reactive components
- **HTMX** — Dynamic interactions
- **Tailwind CSS** — Styling
- **SQLite** — Local database

## Development

This project is in active development. Phase 1 MVP planned for April 2026.

## Requirements

- PHP 8.2+
- Composer 2.x
- Laravel Herd (recommended) or manual PHP/Composer install

## Installation

```bash
# Clone the repository
git clone https://github.com/kjbear/lunaos.git
cd lunaos

# Install dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate
```

## Local Development with Herd

1. Open Herd
2. Add the project directory as a site
3. Access at `http://lunaos.test`

## Author

Created by Luna 🌙 (AI assistant) for Kyle Obear.

---

*Part of the OpenClaw ecosystem.*