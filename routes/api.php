<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\AuthorizationController;
use App\Http\Controllers\Api\Auth\UserManagementController;
use App\Http\Controllers\Api\CampusController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CurriculumPlanController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\FacultyController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\Finance\FinancialClearanceController;
use App\Http\Controllers\Api\Finance\FinancialConceptController;
use App\Http\Controllers\Api\Finance\FinancialHoldController;
use App\Http\Controllers\Api\Finance\StudentChargeController;
use App\Http\Controllers\Api\Finance\StudentPaymentController;
use App\Http\Controllers\Api\GradeChangeRequestController;
use App\Http\Controllers\Api\GradeComponentController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\GradeSheetController;
use App\Http\Controllers\Api\GradingScaleController;
use App\Http\Controllers\Api\GradingScaleLevelController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\ModalityController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\StatusHistoryController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectEnrollmentController;
use App\Http\Controllers\Api\SubjectOfferingController;
use App\Http\Controllers\Api\SubjectOfferingScheduleController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/users', [AuthController::class, 'createUser'])->middleware('permission:users.manage');
    Route::get('auth/roles', [AuthorizationController::class, 'roles'])->middleware('permission:roles.view,users.manage');
    Route::get('auth/permissions', [AuthorizationController::class, 'permissions'])->middleware('permission:roles.view,users.manage');
    Route::apiResource('auth/users', UserManagementController::class)->except(['store'])->middleware('permission:users.manage');

    Route::get('careers/{career}/subjects', [CareerController::class, 'subjects'])->middleware('permission:curriculum.view');
    Route::get('careers/{career}/subject-enrollments', [CareerController::class, 'subjectEnrollments'])->middleware('permission:reports.academic.view');
    Route::get('courses/{course}/subject-enrollments', [CourseController::class, 'subjectEnrollments'])->middleware('permission:reports.academic.view');
    Route::get('groups/{group}/students', [GroupController::class, 'students'])->middleware('permission:students.view');
    Route::get('students/{student}/payment-status', [StudentController::class, 'paymentStatus'])->middleware('permission:finances.view,enrollments.create');
    Route::get('students/{student}/financial-clearance', [FinancialClearanceController::class, 'show'])->middleware('permission:finances.view,enrollments.create');
    Route::get('students/{student}/academic-summary', [StudentController::class, 'academicSummary'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/academic-history', [StudentController::class, 'academicHistory'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/kardex', [StudentController::class, 'kardex'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/grades', [StudentController::class, 'grades'])->middleware('permission:grades.view,academic_history.view');
    Route::patch('finances/{finance}/mark-paid', [FinanceController::class, 'markPaid'])->middleware('permission:finances.payments.validate');
    Route::post('student-charges/{studentCharge}/adjustments', [StudentChargeController::class, 'adjust'])->middleware('permission:finances.manage');

    Route::apiResource('careers', CareerController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('careers', CareerController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('institutions', InstitutionController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('institutions', InstitutionController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('campuses', CampusController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('campuses', CampusController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('faculties', FacultyController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('faculties', FacultyController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('departments', DepartmentController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('departments', DepartmentController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('modalities', ModalityController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('modalities', ModalityController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('courses', CourseController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('courses', CourseController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('subjects', SubjectController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('subjects', SubjectController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('curriculum-plans', CurriculumPlanController::class)->only(['index', 'show'])->middleware('permission:curriculum.view,curriculum.manage');
    Route::apiResource('curriculum-plans', CurriculumPlanController::class)->except(['index', 'show'])->middleware('permission:curriculum.manage');
    Route::apiResource('groups', GroupController::class)->only(['index', 'show'])->middleware('permission:groups.view,groups.manage');
    Route::apiResource('groups', GroupController::class)->except(['index', 'show'])->middleware('permission:groups.manage');
    Route::apiResource('students', StudentController::class)->only(['index', 'show'])->middleware('permission:students.view,students.manage');
    Route::apiResource('students', StudentController::class)->except(['index', 'show'])->middleware('permission:students.manage');
    Route::apiResource('enrollments', EnrollmentController::class)->only(['index', 'show'])->middleware('permission:enrollments.view,enrollments.manage');
    Route::apiResource('enrollments', EnrollmentController::class)->only(['store'])->middleware('permission:enrollments.create,enrollments.manage');
    Route::apiResource('enrollments', EnrollmentController::class)->only(['update', 'destroy'])->middleware('permission:enrollments.manage');
    Route::apiResource('finances', FinanceController::class)->only(['index', 'show'])->middleware('permission:finances.view,finances.manage');
    Route::apiResource('finances', FinanceController::class)->except(['index', 'show'])->middleware('permission:finances.manage');
    Route::apiResource('financial-concepts', FinancialConceptController::class)
        ->parameters(['financial-concepts' => 'financialConcept'])
        ->only(['index', 'show'])
        ->middleware('permission:finances.view,finances.manage');
    Route::apiResource('financial-concepts', FinancialConceptController::class)
        ->parameters(['financial-concepts' => 'financialConcept'])
        ->except(['index', 'show'])
        ->middleware('permission:finances.manage');
    Route::apiResource('student-charges', StudentChargeController::class)
        ->parameters(['student-charges' => 'studentCharge'])
        ->only(['index', 'show'])
        ->middleware('permission:finances.view,finances.manage');
    Route::apiResource('student-charges', StudentChargeController::class)
        ->parameters(['student-charges' => 'studentCharge'])
        ->except(['index', 'show'])
        ->middleware('permission:finances.manage');
    Route::apiResource('student-payments', StudentPaymentController::class)
        ->parameters(['student-payments' => 'studentPayment'])
        ->only(['index', 'show'])
        ->middleware('permission:finances.view,finances.manage');
    Route::apiResource('student-payments', StudentPaymentController::class)
        ->parameters(['student-payments' => 'studentPayment'])
        ->except(['index', 'show'])
        ->middleware('permission:finances.payments.validate,finances.manage');
    Route::apiResource('financial-holds', FinancialHoldController::class)
        ->parameters(['financial-holds' => 'financialHold'])
        ->only(['index', 'show'])
        ->middleware('permission:finances.view,finances.manage');
    Route::apiResource('financial-holds', FinancialHoldController::class)
        ->parameters(['financial-holds' => 'financialHold'])
        ->except(['index', 'show'])
        ->middleware('permission:finances.manage');
    Route::apiResource('professors', ProfessorController::class)->only(['index', 'show'])->middleware('permission:professors.view,professors.manage');
    Route::apiResource('professors', ProfessorController::class)->except(['index', 'show'])->middleware('permission:professors.manage');
    Route::apiResource('subject-enrollments', SubjectEnrollmentController::class)->only(['index', 'show'])->middleware('permission:subject_enrollments.view,subject_enrollments.manage');
    Route::apiResource('subject-enrollments', SubjectEnrollmentController::class)->except(['index', 'show'])->middleware('permission:subject_enrollments.manage');
    Route::apiResource('subject-offerings', SubjectOfferingController::class)
        ->parameters(['subject-offerings' => 'subjectOffering'])
        ->only(['index', 'show'])
        ->middleware('permission:subject_enrollments.view,subject_enrollments.manage');
    Route::apiResource('subject-offerings', SubjectOfferingController::class)
        ->parameters(['subject-offerings' => 'subjectOffering'])
        ->except(['index', 'show'])
        ->middleware('permission:subject_enrollments.manage');
    Route::apiResource('subject-offering-schedules', SubjectOfferingScheduleController::class)
        ->parameters(['subject-offering-schedules' => 'subjectOfferingSchedule'])
        ->middleware('permission:subject_enrollments.manage');
    Route::apiResource('grading-scales', GradingScaleController::class)
        ->parameters(['grading-scales' => 'gradingScale'])
        ->only(['index', 'show'])
        ->middleware('permission:grades.view,grades.manage');
    Route::apiResource('grading-scales', GradingScaleController::class)
        ->parameters(['grading-scales' => 'gradingScale'])
        ->except(['index', 'show'])
        ->middleware('permission:grades.configure');
    Route::apiResource('grading-scale-levels', GradingScaleLevelController::class)
        ->parameters(['grading-scale-levels' => 'gradingScaleLevel'])
        ->middleware('permission:grades.configure');
    Route::apiResource('grade-components', GradeComponentController::class)
        ->parameters(['grade-components' => 'gradeComponent'])
        ->only(['index', 'show'])
        ->middleware('permission:grades.view,grades.manage');
    Route::apiResource('grade-components', GradeComponentController::class)
        ->parameters(['grade-components' => 'gradeComponent'])
        ->except(['index', 'show'])
        ->middleware('permission:grades.configure,grades.manage');
    Route::post('grade-sheets/{gradeSheet}/submit', [GradeSheetController::class, 'submit'])->middleware('permission:grades.manage');
    Route::post('grade-sheets/{gradeSheet}/sign', [GradeSheetController::class, 'sign'])->middleware('permission:grades.sign');
    Route::post('grade-sheets/{gradeSheet}/close', [GradeSheetController::class, 'close'])->middleware('permission:grades.close');
    Route::apiResource('grade-sheets', GradeSheetController::class)
        ->parameters(['grade-sheets' => 'gradeSheet'])
        ->only(['index', 'show'])
        ->middleware('permission:grades.view,grades.manage');
    Route::apiResource('grade-sheets', GradeSheetController::class)
        ->parameters(['grade-sheets' => 'gradeSheet'])
        ->except(['index', 'show'])
        ->middleware('permission:grades.manage');
    Route::post('grade-change-requests/{gradeChangeRequest}/approve', [GradeChangeRequestController::class, 'approve'])->middleware('permission:grades.change.approve');
    Route::post('grade-change-requests/{gradeChangeRequest}/reject', [GradeChangeRequestController::class, 'reject'])->middleware('permission:grades.change.approve');
    Route::apiResource('grade-change-requests', GradeChangeRequestController::class)
        ->parameters(['grade-change-requests' => 'gradeChangeRequest'])
        ->only(['index', 'show', 'store'])
        ->middleware('permission:grades.view,grades.manage');
    Route::apiResource('grades', GradeController::class)->only(['index', 'show'])->middleware('permission:grades.view,grades.manage');
    Route::apiResource('grades', GradeController::class)->except(['index', 'show'])->middleware('permission:grades.manage');
    Route::apiResource('status-histories', StatusHistoryController::class)->only(['index', 'show'])->middleware('permission:audit.view');
});
