<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\AuthorizationController;
use App\Http\Controllers\Api\Auth\UserManagementController;
use App\Http\Controllers\Api\Admissions\AdmissionDecisionController;
use App\Http\Controllers\Api\Admissions\AdmissionInterviewController;
use App\Http\Controllers\Api\Admissions\ApplicantController;
use App\Http\Controllers\Api\Admissions\ApplicationDocumentController;
use App\Http\Controllers\Api\Attendance\AttendanceRecordController;
use App\Http\Controllers\Api\Attendance\AttendanceSummaryController;
use App\Http\Controllers\Api\Attendance\ClassSessionController;
use App\Http\Controllers\Api\CampusController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CurriculumPlanController;
use App\Http\Controllers\Api\DashboardController;
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
use App\Http\Controllers\Api\Reports\AcademicReportController;
use App\Http\Controllers\Api\Reports\FinanceReportController;
use App\Http\Controllers\Api\Reports\ReportExportController;
use App\Http\Controllers\Api\StatusHistoryController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectEnrollmentController;
use App\Http\Controllers\Api\SubjectOfferingController;
use App\Http\Controllers\Api\SubjectOfferingScheduleController;
use App\Http\Controllers\Api\SubjectController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::get('certificates/verify/{verificationCode}', [CertificateController::class, 'verify']);

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
    Route::get('students/check-duplicate', [StudentController::class, 'checkDuplicate'])->middleware('permission:students.view,students.manage,admissions.manage');
    Route::get('students/{student}/payment-status', [StudentController::class, 'paymentStatus'])->middleware('permission:finances.view,enrollments.create');
    Route::get('students/{student}/financial-clearance', [FinancialClearanceController::class, 'show'])->middleware('permission:finances.view,enrollments.create');
    Route::get('students/{student}/academic-summary', [StudentController::class, 'academicSummary'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/academic-history', [StudentController::class, 'academicHistory'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/kardex', [StudentController::class, 'kardex'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/transcript', [StudentController::class, 'transcript'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/gpa', [StudentController::class, 'gpa'])->middleware('permission:academic_history.view');
    Route::get('students/{student}/grades', [StudentController::class, 'grades'])->middleware('permission:grades.view,academic_history.view');
    Route::get('students/{student}/certificates', [CertificateController::class, 'forStudent'])->middleware('permission:reports.academic.view,academic_history.view');
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics'])->middleware('permission:reports.academic.view,reports.finance.view');
    Route::patch('finances/{finance}/mark-paid', [FinanceController::class, 'markPaid'])->middleware('permission:finances.payments.validate');
    Route::post('student-charges/{studentCharge}/adjustments', [StudentChargeController::class, 'adjust'])->middleware('permission:finances.manage');
    Route::post('enrollments/{enrollment}/submit', [EnrollmentController::class, 'submit'])->middleware('permission:enrollments.create,enrollments.manage');
    Route::post('enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->middleware('permission:enrollments.manage');
    Route::post('enrollments/{enrollment}/confirm-payment', [EnrollmentController::class, 'confirmPayment'])->middleware('permission:enrollments.manage,finances.payments.validate');
    Route::post('enrollments/{enrollment}/activate', [EnrollmentController::class, 'activate'])->middleware('permission:enrollments.manage');
    Route::post('student-payments/{studentPayment}/validate', [StudentPaymentController::class, 'validatePayment'])->middleware('permission:finances.payments.validate');
    Route::post('student-payments/{studentPayment}/reject', [StudentPaymentController::class, 'reject'])->middleware('permission:finances.payments.validate');
    Route::post('payments/{studentPayment}/validate', [StudentPaymentController::class, 'validatePayment'])->middleware('permission:finances.payments.validate');
    Route::post('payments/{studentPayment}/reject', [StudentPaymentController::class, 'reject'])->middleware('permission:finances.payments.validate');
    Route::post('applicants/{applicant}/submit', [ApplicantController::class, 'submit'])->middleware('permission:admissions.manage');
    Route::post('applicants/{applicant}/convert-to-student', [ApplicantController::class, 'convert'])->middleware('permission:admissions.manage,students.manage');
    Route::post('class-sessions/{classSession}/generate-attendance', [ClassSessionController::class, 'generateRecords'])->middleware('permission:attendance.manage');
    Route::get('students/{student}/attendance-summary', [AttendanceSummaryController::class, 'student'])->middleware('permission:attendance.view,attendance.manage');

    Route::apiResource('careers', CareerController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('careers', CareerController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('programs', CareerController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('programs', CareerController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
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
    Route::apiResource('academic-periods', CourseController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('academic-periods', CourseController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('subjects', SubjectController::class)->only(['index', 'show'])->middleware('permission:catalogs.view,catalogs.manage');
    Route::apiResource('subjects', SubjectController::class)->except(['index', 'show'])->middleware('permission:catalogs.manage');
    Route::apiResource('curriculum-plans', CurriculumPlanController::class)->only(['index', 'show'])->middleware('permission:curriculum.view,curriculum.manage');
    Route::apiResource('curriculum-plans', CurriculumPlanController::class)->except(['index', 'show'])->middleware('permission:curriculum.manage');
    Route::apiResource('groups', GroupController::class)->only(['index', 'show'])->middleware('permission:groups.view,groups.manage');
    Route::apiResource('groups', GroupController::class)->except(['index', 'show'])->middleware('permission:groups.manage');
    Route::apiResource('students', StudentController::class)->only(['index', 'show'])->middleware('permission:students.view,students.manage');
    Route::apiResource('students', StudentController::class)->except(['index', 'show'])->middleware('permission:students.manage');
    Route::apiResource('applicants', ApplicantController::class)->only(['index', 'show'])->middleware('permission:admissions.manage');
    Route::apiResource('applicants', ApplicantController::class)->except(['index', 'show'])->middleware('permission:admissions.manage');
    Route::apiResource('application-documents', ApplicationDocumentController::class)
        ->parameters(['application-documents' => 'applicationDocument'])
        ->middleware('permission:admissions.manage');
    Route::apiResource('admission-interviews', AdmissionInterviewController::class)
        ->parameters(['admission-interviews' => 'admissionInterview'])
        ->middleware('permission:admissions.manage');
    Route::apiResource('admission-decisions', AdmissionDecisionController::class)
        ->parameters(['admission-decisions' => 'admissionDecision'])
        ->middleware('permission:admissions.manage');
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
    Route::apiResource('payments', StudentPaymentController::class)
        ->parameters(['payments' => 'studentPayment'])
        ->middleware('permission:finances.view,finances.payments.validate,finances.manage');
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
    Route::apiResource('teachers', ProfessorController::class)->only(['index', 'show'])->middleware('permission:professors.view,professors.manage');
    Route::apiResource('teachers', ProfessorController::class)->except(['index', 'show'])->middleware('permission:professors.manage');
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
    Route::get('subject-offerings/{subjectOffering}/students', [SubjectOfferingController::class, 'students'])->middleware('permission:subject_enrollments.view,students.view');
    Route::get('course-groups/{subjectOffering}/students', [SubjectOfferingController::class, 'students'])->middleware('permission:subject_enrollments.view,students.view');
    Route::apiResource('course-groups', SubjectOfferingController::class)
        ->parameters(['course-groups' => 'subjectOffering'])
        ->middleware('permission:subject_enrollments.view,subject_enrollments.manage');
    Route::apiResource('subject-offering-schedules', SubjectOfferingScheduleController::class)
        ->parameters(['subject-offering-schedules' => 'subjectOfferingSchedule'])
        ->middleware('permission:subject_enrollments.manage');
    Route::apiResource('class-sessions', ClassSessionController::class)
        ->parameters(['class-sessions' => 'classSession'])
        ->only(['index', 'show'])
        ->middleware('permission:attendance.view,attendance.manage');
    Route::apiResource('class-sessions', ClassSessionController::class)
        ->parameters(['class-sessions' => 'classSession'])
        ->except(['index', 'show'])
        ->middleware('permission:attendance.manage');
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters(['attendance-records' => 'attendanceRecord'])
        ->only(['index', 'show'])
        ->middleware('permission:attendance.view,attendance.manage');
    Route::apiResource('attendance-records', AttendanceRecordController::class)
        ->parameters(['attendance-records' => 'attendanceRecord'])
        ->except(['index', 'show'])
        ->middleware('permission:attendance.manage');
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
    Route::get('grades/{grade}/audit-logs', [GradeController::class, 'auditLogs'])->middleware('permission:grades.view,grades.manage,audit.view');
    Route::post('certificates/generate', [CertificateController::class, 'generate'])->middleware('permission:reports.academic.view,academic_history.view');
    Route::get('certificates/{certificate}/download', [CertificateController::class, 'download'])->middleware('permission:reports.academic.view,academic_history.view');
    Route::apiResource('certificates', CertificateController::class)->only(['index', 'show', 'update', 'destroy'])->middleware('permission:reports.academic.view,academic_history.view');
    Route::prefix('reports')->group(function (): void {
        Route::get('enrollment-by-period', [AcademicReportController::class, 'enrollmentByPeriod'])->middleware('permission:reports.academic.view');
        Route::get('grades-by-group', [AcademicReportController::class, 'gradesByGroup'])->middleware('permission:reports.academic.view');
        Route::get('grade-sheets', [AcademicReportController::class, 'gradeSheets'])->middleware('permission:reports.academic.view,grades.view');
        Route::get('students/{student}/certificate', [AcademicReportController::class, 'certificate'])->middleware('permission:reports.academic.view,academic_history.view');
        Route::get('students/{student}/certificate/export', [ReportExportController::class, 'certificate'])->middleware('permission:reports.academic.view,academic_history.view');
        Route::get('students/{student}/kardex', [AcademicReportController::class, 'kardex'])->middleware('permission:reports.academic.view,academic_history.view');
        Route::get('students/{student}/kardex/export', [ReportExportController::class, 'kardex'])->middleware('permission:reports.academic.view,academic_history.view');
        Route::get('grade-sheets/{gradeSheet}/export', [ReportExportController::class, 'gradeSheet'])->middleware('permission:reports.academic.view,grades.view');
        Route::get('graduates', [AcademicReportController::class, 'graduates'])->middleware('permission:reports.academic.view');
        Route::get('withdrawals', [AcademicReportController::class, 'withdrawals'])->middleware('permission:reports.academic.view');
        Route::get('retention', [AcademicReportController::class, 'retention'])->middleware('permission:reports.academic.view');
        Route::get('faculty-performance', [AcademicReportController::class, 'facultyPerformance'])->middleware('permission:reports.academic.view');
        Route::get('delinquency', [FinanceReportController::class, 'delinquency'])->middleware('permission:reports.finance.view,finances.view');
        Route::get('delinquency/export', [ReportExportController::class, 'delinquency'])->middleware('permission:reports.finance.view,finances.view');
    });
    Route::apiResource('status-histories', StatusHistoryController::class)->only(['index', 'show'])->middleware('permission:audit.view');
});
