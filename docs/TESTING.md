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

## Current Local Blocker

The tests are written, but local execution requires a complete Composer install. In the current environment, dependency installation timed out while downloading/cloning Laravel packages from GitHub, so `vendor/autoload.php` was not generated.

Once Composer completes, the suite should be executed immediately because it may reveal migration/runtime issues that syntax checks cannot catch.
