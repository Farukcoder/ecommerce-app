<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupportTicket extends Model
{
    protected $fillable = [
        'ticket_number',
        'name',
        'email',
        'phone',
        'subject',
        'order_number',
        'message',
        'status',
        'admin_note',
        'customer_id',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (SupportTicket $ticket): void {
            if ($ticket->ticket_number) {
                return;
            }

            $year = now()->format('Y');
            $nextId = (int) DB::table('support_tickets')->max('id') + 1;
            $ticket->ticket_number = sprintf('TKT-%s-%05d', $year, $nextId);
        });
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getSubjectLabelAttribute(): string
    {
        return config('support.subjects.' . $this->subject, ucfirst(str_replace('_', ' ', $this->subject)));
    }

    public function getStatusLabelAttribute(): string
    {
        return config('support.statuses.' . $this->status, ucfirst(str_replace('_', ' ', $this->status)));
    }
}
