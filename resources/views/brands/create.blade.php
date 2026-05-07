@extends('tyro-dashboard::layouts.user')

@section('title', 'Add Brand')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('brands.index') }}">Brands</a>
<span class="breadcrumb-separator">/</span>
<span>Add Brand</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Add Brand</h1>
            <p class="page-description">Create a new brand for your products.</p>
        </div>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>
</div>

@include('brands._form', [
    'brand'  => null,
    'action' => route('brands.store'),
    'method' => 'POST',
])

@endsection
