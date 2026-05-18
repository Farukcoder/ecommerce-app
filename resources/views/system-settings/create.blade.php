@extends('tyro-dashboard::layouts.admin')

@section('title', 'Create System Setting')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('system-settings.index') }}">System Setting</a>
<span class="breadcrumb-separator">/</span>
<span>Create</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Create System Setting</h1>
            <p class="page-description">Create the first storefront configuration record.</p>
        </div>
    </div>
</div>

@include('system-settings._form', [
    'systemSetting' => $systemSetting,
    'formAction' => route('system-settings.store'),
    'formMethod' => 'POST',
    'submitLabel' => 'Save Setting',
])
@endsection
