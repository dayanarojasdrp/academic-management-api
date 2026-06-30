<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StatusHistory::query()->latest('changed_at');

        if ($request->filled('trackable_type')) {
            $query->where('trackable_type', $request->string('trackable_type')->toString());
        }

        if ($request->filled('trackable_id')) {
            $query->where('trackable_id', $request->integer('trackable_id'));
        }

        return response()->json($query->paginate(50));
    }

    public function show(StatusHistory $statusHistory): JsonResponse
    {
        return response()->json($statusHistory);
    }
}
