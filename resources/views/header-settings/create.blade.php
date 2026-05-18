@extends('tyro-dashboard::layouts.admin')

@section('title', 'Create Header Setting')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('header-settings.index') }}">Header Setting</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create Header Setting</h1>
            <p class="page-description">Create the header configuration record.</p>
        </div>
    </div>
</div>

@include('header-settings._form', [
    'headerSetting' => $headerSetting,
    'formAction' => route('header-settings.store'),
    'formMethod' => 'POST',
    'submitLabel' => 'Save Setting',
])
@endsection
