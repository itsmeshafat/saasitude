@extends('tyro-dashboard::layouts.user')

@section('title', 'Support')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Support</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Support</h1>
            <p class="page-description">Open a ticket and track your requests.</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">New ticket</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.support.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="form-input" required>
                    @error('subject')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="category">Category</label>
                    <select name="category" id="category" class="form-input" required>
                        @foreach(['general' => 'General', 'billing' => 'Billing', 'technical' => 'Technical', 'feature' => 'Feature request'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="body">How can we help?</label>
                    <textarea name="body" id="body" rows="5" class="form-input" required>{{ old('body') }}</textarea>
                    @error('body')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                </div>
                <x-ui.button variant="primary" type="submit">Submit ticket</x-ui.button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">My tickets</h3></div>
        <div class="card-body">
            @forelse($tickets as $ticket)
                <div class="flex items-center justify-between py-2 border-b border-[var(--border)] last:border-0">
                    <a href="{{ route('dashboard.support.show', $ticket) }}" class="hover:underline">
                        <span class="font-medium">{{ $ticket->subject }}</span>
                        <span class="text-xs text-[var(--muted-foreground)] ml-2">{{ $ticket->number }}</span>
                    </a>
                    <x-ui.badge variant="{{ $ticket->status === 'open' ? 'success' : ($ticket->status === 'pending' ? 'warning' : 'muted') }}">
                        {{ ucfirst($ticket->status) }}
                    </x-ui.badge>
                </div>
            @empty
                <x-ui.empty-state title="No tickets yet" description="Your support requests will appear here." />
            @endforelse
        </div>
    </div>
</div>
@endsection
