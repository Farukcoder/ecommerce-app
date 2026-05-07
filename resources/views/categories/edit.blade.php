@extends('tyro-dashboard::layouts.user')

@section('title', 'Edit Category')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('categories.index') }}">Categories</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Category</h1>
            <p class="page-description">Update category details, image or status.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>
</div>

@include('categories._form', [
    'category' => $category,
    'action'   => route('categories.update', $category),
    'method'   => 'PUT',
])

@endsection
