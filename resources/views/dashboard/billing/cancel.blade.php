@extends('tyro-dashboard::layouts.user')

@section('title', 'Checkout Cancelled')

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Checkout cancelled</h1>
            <p class="page-description">No charge was made. You can try again whenever you're ready.</p>
        </div>
    </div>
</div>
<a href="{{ route('dashboard.billing.checkout') }}" class="btn btn-primary">Back to plans</a>
@endsection
