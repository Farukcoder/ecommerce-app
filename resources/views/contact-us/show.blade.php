@extends('tyro-dashboard::layouts.user')

@section('title', 'Contact Message')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('contact-us.index') }}">Contact Us</a>
<span class="breadcrumb-separator">/</span>
<span>#{{ $message->id }}</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Contact Message #{{ $message->id }}</h1>
            <p class="page-description">Submitted {{ $message->created_at?->format('M d, Y h:i A') }}</p>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
            <a href="{{ route('contact-us.index') }}" class="btn btn-ghost">Back</a>
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
            </div>
            <div class="card-body" style="line-height:1.7; white-space:pre-wrap;">{{ $message->message }}</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Internal Note</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('contact-us.note', $message) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <textarea name="admin_note" class="form-input" rows="5" placeholder="Add a private note for your team…">{{ old('admin_note', $message->admin_note) }}</textarea>
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
                    <div style="font-weight:500;">{{ $message->name }}</div>
                </div>
                <div>
                    <div class="form-hint">Email</div>
                    <div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>
                </div>
                <div>
                    <div class="form-hint">Phone</div>
                    <div>{{ $message->phone }}</div>
                </div>
                @if($message->customer)
                    <div>
                        <div class="form-hint">Linked Account</div>
                        <div>{{ $message->customer->name }} ({{ $message->customer->email }})</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body">
                <form action="{{ route('contact-us.status', $message) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <select name="status" class="form-select">
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ $message->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    @if($message->read_at)
                        <p class="form-hint" style="margin-bottom:0.5rem;">
                            Read {{ $message->read_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                    @if($message->replied_at)
                        <p class="form-hint" style="margin-bottom:0.75rem;">
                            Replied {{ $message->replied_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                    <button type="submit" class="btn btn-primary" style="width:100%;">Update Status</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
