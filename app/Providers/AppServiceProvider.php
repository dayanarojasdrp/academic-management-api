<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\GradeChangeRequest;
use App\Models\GradeSheet;
use App\Models\Student;
use App\Models\SubjectEnrollment;
use App\Policies\EnrollmentPolicy;
use App\Policies\GradeChangeRequestPolicy;
use App\Policies\GradePolicy;
use App\Policies\GradeSheetPolicy;
use App\Policies\StudentPolicy;
use App\Policies\SubjectEnrollmentPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(SubjectEnrollment::class, SubjectEnrollmentPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(GradeSheet::class, GradeSheetPolicy::class);
        Gate::policy(GradeChangeRequest::class, GradeChangeRequestPolicy::class);
    }
}
