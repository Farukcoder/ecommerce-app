<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    /**
     * Subject options for the storefront contact form.
     */
    public function subjects(): JsonResponse
    {
        $subjects = collect(config('support.subjects', []))
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        return response()->json(['data' => $subjects]);
    }

    /**
     * Store a support ticket from the public website contact form.
     */
    public function store(Request $request): JsonResponse
    {
        $subjectKeys = array_keys(config('support.subjects', []));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', Rule::in($subjectKeys)],
            'order_number' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $customerId = $request->user('sanctum')?->id;

        $ticket = SupportTicket::query()->create([
            ...$data,
            'order_number' => filled($data['order_number'] ?? null) ? $data['order_number'] : null,
            'status' => 'open',
            'customer_id' => $customerId,
        ]);

        return response()->json([
            'message' => 'Your message has been sent. We will get back to you soon.',
            'data' => [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at?->toISOString(),
            ],
        ], 201);
    }
}
