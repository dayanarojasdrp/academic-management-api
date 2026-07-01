# Academic Management API

Cloud-ready REST API in Laravel for academic management in higher education institutions, designed around Latin American institutional workflows: admissions, enrollment, finance, attendance, grades, certificates, reports and role-based governance.

## Core Modules

- Authentication with Laravel Sanctum.
- Roles and permissions for institutional users.
- Students, applicants and admissions.
- Institutions, campuses, faculties, departments, modalities, careers and academic periods.
- Enrollment with financial validation.
- Student charges, payments, receipts and financial clearance.
- Curriculum plans, subjects, offerings, groups and schedules.
- Attendance by class session.
- Grades, grade sheets, grade audit logs and academic history.
- Certificates with immutable snapshot data and verification code.
- Dashboard metrics and official PDF/CSV exports.

## Requirements

Local without Docker:

- PHP 8.2+
- Composer 2+
- MySQL 8+ or compatible MariaDB
- Redis 7+ recommended

Local with Docker:

- Docker
- Docker Compose

## Quick Start With Docker And MySQL

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

API:

```text
http://localhost:8000/api
```

Health checks:

```bash
curl http://localhost:8000/api/health
curl http://localhost:8000/api/health/deep
```

Logs:

```bash
docker compose logs -f app
docker compose logs -f mysql
```

Tests:

```bash
composer run test:smoke
```

Full PHPUnit suite, after installing dev dependencies:

```bash
composer install
php artisan test
```

Inside Docker:

```bash
docker compose exec app composer run test:smoke
docker compose exec app composer install
docker compose exec app php artisan test
```

## Use An Existing Local MySQL

If MySQL is already installed on your machine, set this in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_management
DB_USERNAME=your_mysql_user
DB_PASSWORD=your_mysql_password
```

Then run:

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Cloud-Native Defaults

The project is prepared for deployable environments:

- MySQL is the default database.
- Redis is prepared for cache and queues.
- Logs are sent to `stderr` in Docker through `LOG_STACK=stderr`.
- CORS is configured through `CORS_ALLOWED_ORIGINS`.
- `/api/health` is available for load balancers.
- `/api/health/deep` checks database and cache.
- `.env.example` contains production-relevant variables.

## Important Environment Variables

```env
APP_URL=https://api.example.edu
FRONTEND_URL=https://app.example.edu
CORS_ALLOWED_ORIGINS=https://app.example.edu,http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=academic_management
DB_USERNAME=academic_user
DB_PASSWORD=change_me

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

LOG_CHANNEL=stack
LOG_STACK=stderr
LOG_LEVEL=info
```

## Documentation

- Frontend contract: [docs/FRONTEND_CONTRACT.md](docs/FRONTEND_CONTRACT.md)
- Demo data and manual QA: [docs/DEMO_DATA.md](docs/DEMO_DATA.md)
- API guide: [docs/API.md](docs/API.md)
- Architecture: [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)
- Authorization: [docs/AUTHORIZATION.md](docs/AUTHORIZATION.md)
- Finance: [docs/FINANCE.md](docs/FINANCE.md)
- Gradebook: [docs/GRADEBOOK.md](docs/GRADEBOOK.md)
- Official reports: [docs/OFFICIAL_REPORTS.md](docs/OFFICIAL_REPORTS.md)
- Admissions, attendance and exports: [docs/ADMISSIONS_ATTENDANCE_EXPORTS.md](docs/ADMISSIONS_ATTENDANCE_EXPORTS.md)
- Testing: [docs/TESTING.md](docs/TESTING.md)

## Useful Commands

```bash
php artisan route:list --path=api
php artisan migrate:fresh --seed
composer run test:smoke
php artisan test
php artisan config:clear
php artisan cache:clear
```

With Docker:

```bash
docker compose exec app php artisan route:list --path=api
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app composer run test:smoke
docker compose exec app php artisan test
```

## Research Positioning

This backend is intended to support a cloud-native academic management research proposal for Latin American higher education institutions. It focuses on institutional workflows, financial enrollment control, auditable academic records, official documentation and deployable infrastructure.
