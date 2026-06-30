<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusHistory;
use App\Support\ApiQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    protected string $modelClass;

    protected array $relations = [];

    public function index(Request $request): JsonResponse
    {
        $records = $this->modelClass::query()
            ->with($this->relations)
            ->orderByDesc('id');

        return response()->json(ApiQuery::paginate($records, $request));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules());
        $record = new $this->modelClass();
        $record->fill(array_intersect_key($validated, array_flip($record->getFillable())));
        $record->save();
        $this->afterSave($record, $request);
        $this->recordStatusChange($record, null, $record->getAttribute('status'), $request);

        return response()->json($record->load($this->relations), 201);
    }

    protected function showRecord(Model $record): JsonResponse
    {
        return response()->json($record->load($this->relations));
    }

    protected function updateRecord(Request $request, Model $record): JsonResponse
    {
        $previousStatus = $record->getAttribute('status');
        $validated = $request->validate($this->rules($record));
        $record->fill(array_intersect_key($validated, array_flip($record->getFillable())));
        $record->save();
        $this->afterSave($record, $request);
        $this->recordStatusChange($record, $previousStatus, $record->getAttribute('status'), $request);

        return response()->json($record->fresh()->load($this->relations));
    }

    protected function destroyRecord(Model $record): JsonResponse
    {
        $record->delete();

        return response()->json(status: 204);
    }

    protected function afterSave(Model $record, Request $request): void
    {
        //
    }

    protected function recordStatusChange(Model $record, ?string $previousStatus, ?string $newStatus, Request $request): void
    {
        if (! $newStatus || ! in_array('status', $record->getFillable(), true)) {
            return;
        }

        if ($record->exists && $previousStatus === $newStatus) {
            return;
        }

        StatusHistory::create([
            'trackable_type' => $record::class,
            'trackable_id' => $record->getKey(),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reason' => $request->input('status_reason'),
            'metadata' => $request->input('status_metadata'),
            'changed_at' => now(),
        ]);
    }

    abstract protected function rules(?Model $record = null): array;
}
