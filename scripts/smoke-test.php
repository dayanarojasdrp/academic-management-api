<?php

use App\Models\Certificate;
use App\Models\Student;
use App\Services\Reports\DocumentExportService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

require __DIR__.'/../vendor/autoload.php';

$database = sys_get_temp_dir().'/academic-management-smoke.sqlite';
if (file_exists($database)) {
    unlink($database);
}
touch($database);

putenv('APP_ENV=testing');
putenv('APP_DEBUG=false');
putenv('DB_CONNECTION=sqlite');
putenv('DB_DATABASE='.$database);
putenv('CACHE_STORE=array');
putenv('QUEUE_CONNECTION=sync');
putenv('SESSION_DRIVER=array');
$_ENV['APP_ENV'] = 'testing';
$_ENV['DB_CONNECTION'] = 'sqlite';
$_ENV['DB_DATABASE'] = $database;
$_ENV['CACHE_STORE'] = 'array';
$_ENV['QUEUE_CONNECTION'] = 'sync';
$_ENV['SESSION_DRIVER'] = 'array';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$checks = [];

$exitCode = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
$checks['migrate_seed'] = $exitCode === 0;
$checks['api_routes'] = Route::getRoutes()->count() >= 250;
$checks['students_seeded'] = DB::table('students')->count() >= 1;
$checks['roles_seeded'] = DB::table('roles')->count() >= 17;
$checks['certificates_seeded'] = DB::table('certificates')->count() >= 1;
$checks['grade_audit_seeded'] = DB::table('grade_audit_logs')->count() >= 1;

$student = Student::query()->first();
$certificate = Certificate::query()->first();
$checks['student_model_loads'] = $student !== null;
$checks['certificate_model_loads'] = $certificate !== null;

if ($certificate) {
    $exporter = $app->make(DocumentExportService::class);
    $response = $exporter->pdf(
        'smoke-certificate',
        'Smoke Certificate',
        $exporter->linesFromPayload('Smoke Certificate', $certificate->snapshot_data)
    );
    $checks['pdf_export'] = str_starts_with($response->getContent(), '%PDF-1.4');
}

$failed = array_keys(array_filter($checks, fn (bool $passed): bool => ! $passed));

foreach ($checks as $name => $passed) {
    echo sprintf("[%s] %s\n", $passed ? 'PASS' : 'FAIL', $name);
}

if ($failed !== []) {
    fwrite(STDERR, 'Smoke test failed: '.implode(', ', $failed).PHP_EOL);
    exit(1);
}

echo "Smoke test completed successfully.\n";
