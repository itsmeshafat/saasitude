@extends('tyro-dashboard::layouts.user')

@section('title', 'Subscription Activated')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">You're all set!</h1>
            <p class="page-description">Your subscription is now active. Features will be available shortly.</p>
        </div>
    </div>
</div>
<a href="{{ route($dashboardRoute::name('index')) }}" class="btn btn-primary">Go to dashboard →</a>
@endsection
