<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::query()->with('customer');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($subject = $request->string('subject')->toString()) {
            $query->where('subject', $subject);
        }

        if ($dateFrom = $request->string('date_from')->toString()) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->string('date_to')->toString()) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('order_number', 'like', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        $summary = [
            'open' => SupportTicket::query()->where('status', 'open')->count(),
            'in_progress' => SupportTicket::query()->where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::query()->where('status', 'resolved')->count(),
            'today' => SupportTicket::query()->whereDate('created_at', now()->toDateString())->count(),
        ];

        $filters = $request->only(['search', 'status', 'subject', 'date_from', 'date_to']);
        $subjects = config('support.subjects', []);
        $statuses = config('support.statuses', []);

        return view('support-tickets.index', compact('tickets', 'summary', 'filters', 'subjects', 'statuses'));
    }

    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load('customer');
        $statuses = config('support.statuses', []);

        return view('support-tickets.show', [
            'ticket' => $supportTicket,
            'statuses' => $statuses,
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket)
    {
        $statusKeys = array_keys(config('support.statuses', []));

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in($statusKeys)],
        ]);

        $supportTicket->status = $data['status'];

        if (in_array($data['status'], ['resolved', 'closed'], true)) {
            $supportTicket->resolved_at = $supportTicket->resolved_at ?? now();
        } else {
            $supportTicket->resolved_at = null;
        }

        $supportTicket->save();

        return redirect()
            ->route('support-tickets.show', $supportTicket)
            ->with('success', 'Ticket status updated.');
    }

    public function updateAdminNote(Request $request, SupportTicket $supportTicket)
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:5000'],
        ]);

        $supportTicket->update([
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return redirect()
            ->route('support-tickets.show', $supportTicket)
            ->with('success', 'Internal note saved.');
    }
}
