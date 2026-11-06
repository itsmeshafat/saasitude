@extends('tyro-dashboard::layouts.user')

@section('title', 'Cancel Subscription')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<a href="{{ route('dashboard.billing') }}">Billing</a>
<span class="breadcrumb-separator">/</span>
<span>Cancel Subscription</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Cancel Subscription</h1>
            <p class="page-description">
                You're on <strong>{{ $subscription->plan->name }}</strong>.
                Cancelling keeps access until the end of the current billing period
                ({{ optional($subscription->current_period_end)->format('M j, Y') ?? 'the period end' }}).
            </p>
        </div>
    </div>
</div>


@if($downgradeOptions->isNotEmpty())
<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Before you go — consider a cheaper plan</h3></div>
    <div class="card-body">
        <p class="text-sm text-[var(--muted-foreground)] mb-3">
            Downgrading keeps your workspace and data instead of cancelling outright.
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach($downgradeOptions as $plan)
                <x-ui.button href="{{ route('dashboard.billing.checkout') }}" variant="outline">Switch to {{ $plan->name }}</x-ui.button>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header"><h3 class="card-title">Why are you cancelling?</h3></div>
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.cancel-subscription.cancel') }}"
              id="cancel-form" x-data>
            @csrf
            <fieldset class="space-y-3 mb-4">
                <legend class="text-sm font-medium mb-2">Tell us why (required):</legend>
                @php $reasons = [
                    'too_expensive' => 'Too expensive',
                    'missing_features' => 'Missing features I need',
                    'switching_competitor' => 'Switching to a competitor',
                    'pausing' => 'Pausing usage for now',
                    'other' => 'Other reason',
                ] @endphp
                @foreach($reasons as $value => $label)
                    <label class="flex items-center gap-3 text-sm cursor-pointer">
                        <input type="radio" name="reason" value="{{ $value }}" class="form-radio"
                               @checked(old('reason') === $value) required>
                        {{ $label }}
                    </label>
                @endforeach
                @error('reason')
                    <p class="text-sm text-[var(--destructive)]">{{ $message }}</p>
                @enderror
            </fieldset>

            <x-ui.button variant="danger" type="button"
                x-on:click="showDanger('Cancel subscription?', 'Your plan stays active until the end of the current billing period, then moves to the free tier.', () => document.getElementById('cancel-form').submit())">
                Cancel my subscription
            </x-ui.button>
        </form>
    </div>
</div>
@endsection
