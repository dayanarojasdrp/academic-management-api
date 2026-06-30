<?php

use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CurriculumPlanController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\ProfessorController;
use App\Http\Controllers\Api\StatusHistoryController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectEnrollmentController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);

Route::get('careers/{career}/subjects', [CareerController::class, 'subjects']);
Route::get('careers/{career}/subject-enrollments', [CareerController::class, 'subjectEnrollments']);
Route::get('courses/{course}/subject-enrollments', [CourseController::class, 'subjectEnrollments']);
Route::get('groups/{group}/students', [GroupController::class, 'students']);
Route::get('students/{student}/payment-status', [StudentController::class, 'paymentStatus']);
Route::get('students/{student}/academic-history', [StudentController::class, 'academicHistory']);
Route::patch('finances/{finance}/mark-paid', [FinanceController::class, 'markPaid']);

Route::apiResources([
    'careers' => CareerController::class,
    'courses' => CourseController::class,
    'curriculum-plans' => CurriculumPlanController::class,
    'enrollments' => EnrollmentController::class,
    'finances' => FinanceController::class,
    'grades' => GradeController::class,
    'groups' => GroupController::class,
    'professors' => ProfessorController::class,
    'subject-enrollments' => SubjectEnrollmentController::class,
    'students' => StudentController::class,
    'subjects' => SubjectController::class,
]);

Route::apiResource('status-histories', StatusHistoryController::class)->only(['index', 'show']);
