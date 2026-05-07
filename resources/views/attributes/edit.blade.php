@extends('tyro-dashboard::layouts.user')

@section('title', 'Edit Attribute')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('attributes.index') }}">Attributes</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Attribute</h1>
            <p class="page-description">Rename the attribute or manage its values.</p>
        </div>
        <a href="{{ route('attributes.index') }}" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>
</div>

@include('attributes._form', [
    'attribute' => $attribute,
    'action'    => route('attributes.update', $attribute),
    'method'    => 'PUT',
])

@endsection
