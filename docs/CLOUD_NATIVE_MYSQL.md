# Cloud-Native MySQL Deployment Notes

The official database target for this project is MySQL 8+.

## Local Docker Topology

`docker-compose.yml` starts:

- `app`: Laravel API.
- `mysql`: MySQL 8.4 with `utf8mb4_unicode_ci`.
- `redis`: cache and queue backend.

Start:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

## Existing MySQL

Use your own MySQL by changing `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_management
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Then:

```bash
php artisan migrate:fresh --seed
```

## Health Checks

Basic:

```bash
curl http://localhost:8000/api/health
```

Deep:

```bash
curl http://localhost:8000/api/health/deep
```

`/api/health/deep` checks database and cache, returning `503` if a critical dependency fails.

## CORS

Frontend origins are configured with:

```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,http://localhost:5173,https://frontend.example.edu
```

The API exposes `Content-Disposition` so frontend can download PDF/CSV files.

## Logs

Docker uses:

```env
LOG_CHANNEL=stack
LOG_STACK=stderr
```

View logs:

```bash
docker compose logs -f app
```

## MySQL Compatibility Notes

Dashboard date calculations use database-driver-specific SQL. MySQL uses:

```sql
timestampdiff(second, enrollment_date, updated_at) / 86400
```

This avoids SQLite-only functions in production paths.

## Production Recommendations

- Use managed MySQL with automated backups.
- Set `APP_DEBUG=false`.
- Set `LOG_LEVEL=info`.
- Use Redis for cache and queues.
- Run `php artisan config:cache` during image build or release.
- Run migrations as a release step, not on every container boot, unless the platform explicitly supports one-off release commands.
- Keep `/api/health` public for the load balancer.
- Restrict `/api/health/deep` at the edge if infrastructure details should not be public.
