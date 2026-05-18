@extends('tyro-dashboard::layouts.admin')

@section('title', 'Edit Header Setting')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('header-settings.index') }}">Header Setting</a>
<span class="breadcrumb-separator">/</span>
<span>Edit</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Edit Header Setting</h1>
            <p class="page-description">Update the storefront header configuration.</p>
        </div>
    </div>
</div>

@include('header-settings._form', [
    'headerSetting' => $headerSetting,
    'formAction' => route('header-settings.update', $headerSetting),
    'formMethod' => 'PUT',
    'submitLabel' => 'Update Setting',
])
@endsection
