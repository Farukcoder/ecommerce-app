@extends('tyro-dashboard::layouts.user')

@section('title', 'Support Ticket')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('support-tickets.index') }}">Support & Communication</a>
<span class="breadcrumb-separator">/</span>
<span>{{ $ticket->ticket_number }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">{{ $ticket->ticket_number }}</h1>
            <p class="page-description">Submitted {{ $ticket->created_at?->format('M d, Y h:i A') }}</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('support-tickets.index') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1rem;">{{ session('success') }}</div>
@endif

<div style="display:grid; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); gap: 1.5rem; align-items:start;">
    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Message</h3>
                <span class="badge badge-secondary">{{ $ticket->subject_label }}</span>
            </div>
            <div class="card-body" style="line-height:1.7; white-space:pre-wrap;">{{ $ticket->message }}</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Internal Note</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('support-tickets.note', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <textarea name="admin_note" class="form-input" rows="5" placeholder="Add a private note for your team…">{{ old('admin_note', $ticket->admin_note) }}</textarea>
                        @error('admin_note')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-secondary">Save Note</button>
                </form>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:1rem;">
            <div class="card-header">
                <h3 class="card-title">Contact</h3>
            </div>
            <div class="card-body" style="display:grid; gap:0.75rem;">
                <div>
                    <div class="form-hint">Name</div>
                    <div style="font-weight:500;">{{ $ticket->name }}</div>
                </div>
                <div>
                    <div class="form-hint">Email</div>
                    <div><a href="mailto:{{ $ticket->email }}">{{ $ticket->email }}</a></div>
                </div>
                <div>
                    <div class="form-hint">Phone</div>
                    <div>{{ $ticket->phone }}</div>
                </div>
                @if($ticket->order_number)
                    <div>
                        <div class="form-hint">Order Number</div>
                        <div style="font-weight:500;">{{ $ticket->order_number }}</div>
                    </div>
                @endif
                @if($ticket->customer)
                    <div>
                        <div class="form-hint">Linked Account</div>
                        <div>{{ $ticket->customer->name }} ({{ $ticket->customer->email }})</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('support-tickets.status', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <select name="status" class="form-select">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $ticket->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($ticket->resolved_at)
                        <p class="form-hint" style="margin-bottom:0.75rem;">
                            Resolved {{ $ticket->resolved_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
