# Testing Strategy

The project now includes Feature tests for the core academic and financial rules that make the API credible.

## Covered Scenarios

Authentication and authorization:

- login returns token, roles and permissions.

Enrollment and finance:

- enrollment is blocked when the student has required unpaid debt;
- enrollment is allowed when the student has financial clearance.

Subject enrollment:

- subject outside the current curriculum plan is rejected;
- full subject offering capacity is rejected;
- schedule conflict is rejected.

Gradebook:

- published final passing grade marks subject enrollment as `passed`;
- published final failing grade marks subject enrollment as `failed`;
- closed/locked grade sheets prevent normal grade changes.

## How To Run

```bash
composer install
php artisan test --testsuite=Feature
```

The test environment uses SQLite in memory through `phpunit.xml`.

## Migration Verification

Production dependencies were installed locally with Composer, generating `vendor/autoload.php` and allowing Laravel to boot.

The database bootstrap has been verified with a temporary SQLite database:

```bash
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=/tmp/academic-management-verify.sqlite php artisan migrate:fresh --seed --force
APP_ENV=testing DB_CONNECTION=sqlite DB_DATABASE=/tmp/academic-management-verify.sqlite php artisan route:list --path=api
```

The verification currently loads 173 API routes and seeds the baseline academic data, including users, roles, permissions, one institution, one student, one enrollment, one subject offering and one grade sheet.

## Current Local Blocker

Feature tests require the dev dependencies from Composer, especially PHPUnit and Laravel's testing tooling. If the local install was made with `--no-dev`, run a full `composer install` before executing `php artisan test --testsuite=Feature`.

In this local verification, production dependencies installed correctly, but the full dev dependency install stalled while downloading `laravel/pint`; therefore `php artisan test` is still unavailable in this machine until Composer completes the dev install.
