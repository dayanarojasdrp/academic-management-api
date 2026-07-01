<?php

namespace App\Http\Controllers\Api;

use App\Models\Certificate;
use App\Models\Student;
use App\Services\Certificates\CertificateService;
use App\Services\Reports\DocumentExportService;
use App\Support\ApiQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends ApiController
{
    protected string $modelClass = Certificate::class;

    protected array $relations = ['student.group.career', 'course', 'enrollment', 'generatedBy'];

    public function index(Request $request): JsonResponse
    {
        $query = Certificate::query()->with($this->relations)->orderByDesc('generated_at')->orderByDesc('id');

        ApiQuery::applyEquals($query, $request, [
            'student_id' => 'student_id',
            'type' => 'type',
            'course_id' => 'course_id',
            'enrollment_id' => 'enrollment_id',
            'status' => 'status',
        ]);

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function show(Certificate $certificate): JsonResponse
    {
        return $this->showRecord($certificate);
    }

    public function generate(Request $request, CertificateService $service): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'string', 'max:60'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'enrollment_id' => ['nullable', 'exists:enrollments,id'],
            'purpose' => ['nullable', 'string', 'max:255'],
        ]);

        $certificate = $service->generate($validated, $request);
        $this->recordStatusChange($certificate, null, $certificate->status, $request);

        return response()->json($certificate->load($this->relations), 201);
    }

    public function forStudent(Student $student, Request $request): JsonResponse
    {
        $query = $student->certificates()->with(['course', 'enrollment', 'generatedBy'])->orderByDesc('generated_at');

        ApiQuery::applyEquals($query, $request, [
            'type' => 'type',
            'status' => 'status',
        ]);

        return response()->json(ApiQuery::paginate($query, $request));
    }

    public function download(Certificate $certificate, Request $request, DocumentExportService $exporter): Response
    {
        $title = 'Certificado '.$certificate->certificate_code;
        $format = strtolower($request->query('format', 'pdf'));

        if ($format === 'csv' || $format === 'excel') {
            return $exporter->csv('certificate-'.$certificate->certificate_code, [$certificate->snapshot_data]);
        }

        return $exporter->pdf('certificate-'.$certificate->certificate_code, $title, $exporter->linesFromPayload($title, $certificate->snapshot_data));
    }

    public function verify(string $verificationCode): JsonResponse
    {
        $certificate = Certificate::query()
            ->where('verification_code', $verificationCode)
            ->with(['student:id,student_code,first_name,last_name', 'course:id,name'])
            ->firstOrFail();

        return response()->json([
            'valid' => $certificate->status === 'generated',
            'certificate' => $certificate,
        ]);
    }

    protected function rules(?\Illuminate\Database\Eloquent\Model $record = null): array
    {
        return [
            'status' => ['nullable', 'string', 'max:30'],
            'file_path' => ['nullable', 'string', 'max:255'],
        ];
    }
}
