# Expense Tracker

Expense Tracker is a personal finance tracker built with Laravel + Vue 3.
It helps users track expenses, categories, monthly summaries, and profile settings from a clean dashboard.

## Features

- Authentication (login/register/logout)
- Category management (income/expense)
- Expense tracking
- Monthly summary with category breakdown charts
- Profile settings page
  - Update name, email, phone, preferred currency, and bio
  - Update password securely

## Tech Stack

- Backend: Laravel 11, Sanctum
- Frontend: Vue 3, Pinia, Vite, Tailwind
- Charts: Chart.js + vue-chartjs
- Database: SQLite (local) / PostgreSQL (Render free tier)

## Local Setup

1. Install dependencies:

```bash
composer install
yarn install
```

2. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Run migrations:

```bash
php artisan migrate
```

4. Start app:

```bash
composer run dev
```

## Render Deployment (Production)

This repository includes:

- `Dockerfile`
- `render.yaml`

### Steps

1. Push your repository to GitHub.
2. In Render, create a new **Blueprint** deployment and select this repo.
3. Render will detect `render.yaml` and create the web service.
4. In Render, create a free PostgreSQL instance and copy its connection values.
5. Set required environment variables in Render dashboard:
   - `APP_URL`
   - `APP_KEY`
   - `DB_HOST`
   - `DB_PORT`
   - `DB_DATABASE`
   - `DB_USERNAME`
   - `DB_PASSWORD`
6. Generate your app key locally and paste it into Render (`APP_KEY`):

```bash
php artisan key:generate --show
```

7. Deploy.

## Production Notes

- `APP_DEBUG=false` in production.
- Use strong `APP_KEY` and database credentials.
- `DB_CONNECTION` is set to `pgsql` in `render.yaml`.
- Run with HTTPS only.
- Configure backups for your production database.
