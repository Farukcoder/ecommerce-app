@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit System Setting')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('system-settings.index') }}">System Setting</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit System Setting</h1>
            <p class="page-description">Update the current storefront identity and visual configuration.</p>
        </div>
    </div>
</div>

@include('system-settings._form', [
    'systemSetting' => $systemSetting,
    'formAction' => route('system-settings.update', $systemSetting),
    'formMethod' => 'PUT',
    'submitLabel' => 'Update Setting',
])
@endsection
