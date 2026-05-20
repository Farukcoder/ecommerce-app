<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    public function update(Request $request, Refund $refund, OrderWorkflowService $workflow): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'rejected', 'completed'])],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $updated = $workflow->updateRefundStatus($refund, $data['status'], $data['note'] ?? null, $request->user()?->id);

        return response()->json([
            'message' => 'Refund status updated.',
            'data' => $updated,
        ]);
    }
}
