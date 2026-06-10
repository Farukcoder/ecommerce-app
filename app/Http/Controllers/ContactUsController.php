<?php

namespace App\Http\Controllers;

use App\Models\ContactUsMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactUsController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactUsMessage::query()->with('customer');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'new' => ContactUsMessage::query()->where('status', 'new')->count(),
            'read' => ContactUsMessage::query()->where('status', 'read')->count(),
            'replied' => ContactUsMessage::query()->where('status', 'replied')->count(),
            'today' => ContactUsMessage::query()->whereDate('created_at', now()->toDateString())->count(),
        ];

        $filters = $request->only(['search', 'status', 'date_from', 'date_to']);
        $statuses = config('contact.statuses', []);

        return view('contact-us.index', compact('messages', 'summary', 'filters', 'statuses'));
    }

    public function show(ContactUsMessage $contactUsMessage)
    {
        $contactUsMessage->load('customer');

        if ($contactUsMessage->status === 'new') {
            $contactUsMessage->update([
                'status' => 'read',
                'read_at' => $contactUsMessage->read_at ?? now(),
            ]);
            $contactUsMessage->refresh();
        }

        $statuses = config('contact.statuses', []);

        return view('contact-us.show', [
            'message' => $contactUsMessage,
            'statuses' => $statuses,
        ]);
    }

    public function updateStatus(Request $request, ContactUsMessage $contactUsMessage)
    {
        $statusKeys = array_keys(config('contact.statuses', []));

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in($statusKeys)],
        ]);

        $contactUsMessage->status = $data['status'];

        if ($data['status'] === 'read' && ! $contactUsMessage->read_at) {
            $contactUsMessage->read_at = now();
        }

        if ($data['status'] === 'replied') {
            $contactUsMessage->replied_at = $contactUsMessage->replied_at ?? now();
            $contactUsMessage->read_at = $contactUsMessage->read_at ?? now();
        }

        if (in_array($data['status'], ['new'], true)) {
            $contactUsMessage->read_at = null;
            $contactUsMessage->replied_at = null;
        }

        if ($data['status'] === 'archived') {
            $contactUsMessage->read_at = $contactUsMessage->read_at ?? now();
        }

        $contactUsMessage->save();

        return redirect()
            ->route('contact-us.show', $contactUsMessage)
            ->with('success', 'Message status updated.');
    }

    public function updateAdminNote(Request $request, ContactUsMessage $contactUsMessage)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $contactUsMessage->update([
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return redirect()
            ->route('contact-us.show', $contactUsMessage)
            ->with('success', 'Internal note saved.');
    }
}
