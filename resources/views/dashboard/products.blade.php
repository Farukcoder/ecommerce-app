@extends('tyro-dashboard::layouts.user')

@section('title', 'Products')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Products</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Products</h1>
            <p class="page-description" style="font-size: 1rem;">Welcome to your new page.</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title" style="font-size: 1.0625rem;">Products Content</h3>
    </div>
    <div class="card-body">
        <p>This is a new user dashboard page. Start building your content here.</p>
    </div>
</div>
@endsection
