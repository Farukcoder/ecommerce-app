<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactUsMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    /**
     * Store a contact us message from the public website form.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $customerId = $request->user('sanctum')?->id;

        $message = ContactUsMessage::query()->create([
            ...$data,
            'status' => 'new',
            'customer_id' => $customerId,
        ]);

        return response()->json([
            'message' => 'Thank you for contacting us. We will get back to you soon.',
            'data' => [
                'id' => $message->id,
                'status' => $message->status,
                'created_at' => $message->created_at?->toISOString(),
            ],
        ], 201);
    }
}
