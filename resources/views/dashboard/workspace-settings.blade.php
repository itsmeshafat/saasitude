@extends('tyro-dashboard::layouts.user')

@section('title', 'Workspace Settings')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Workspace</span>
@endsection

@section('content')
<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Workspace Settings</h1>
            <p class="page-description">Manage {{ $workspace->name }} — naming, ownership and members.</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="card">
        <div class="card-header"><h3 class="card-title">General</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.workspace-settings.update') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="name">Workspace name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $workspace->name) }}" class="form-input" required maxlength="255">
                    @error('name')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="mb-4 text-sm text-[var(--muted-foreground)]">
                    Created {{ $workspace->created_at?->format('M j, Y') }}
                    @if($workspace->is_personal) · personal workspace @endif
                </div>
                <x-ui.button variant="primary" type="submit">Save changes</x-ui.button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Plan &amp; usage</h3></div>
        <div class="card-body">
            @php($subscription = $workspace->activeSubscription())
            @if($subscription)
                <div class="flex items-center gap-2 mb-3">
                    <span class="font-semibold">{{ $subscription->plan?->name ?? '—' }}</span>
                    <x-ui.status-pill :status="$subscription->status" />
                </div>
                <a href="{{ route('dashboard.plans') }}" class="text-sm text-[var(--primary)] hover:underline">Change plan →</a>
            @else
                <x-ui.empty-state title="No active plan" description="Choose a plan to unlock features.">
                    <x-slot:action>
                        <a href="{{ route('dashboard.plans') }}"><x-ui.button>View plans</x-ui.button></a>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        </div>
    </div>
</div>

@php($members = $workspace->workspaceMembers()->with('user')->get())
@if($members->count() > 1)
<div class="card mt-6 border-[var(--destructive)]/40">
    <div class="card-header">
        <div>
            <h3 class="card-title text-[var(--destructive)]">Danger zone</h3>
            <p class="text-sm text-[var(--muted-foreground)]">Transfer ownership to another member. You will become an admin.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('dashboard.workspace-settings.transfer') }}"
              x-data @submit.prevent="showDanger('Transfer ownership?', 'The selected member becomes the workspace owner. This cannot be undone here.', () => $el.submit())">
            @csrf
            <div class="flex flex-wrap gap-3 items-end">
                <div class="flex-1" style="min-width: 240px;">
                    <label class="block text-sm font-medium mb-1" for="new_owner_id">New owner</label>
                    <select name="new_owner_id" id="new_owner_id" class="form-select" required>
                        @foreach($members as $member)
                            @if($member->user_id !== auth()->id())
                                <option value="{{ $member->user_id }}">{{ $member->user?->name }} ({{ $member->user?->email }})</option>
                            @endif
                        @endforeach
                    </select>
                    @error('new_owner_id')<p class="text-sm text-[var(--destructive)] mt-1">{{ $message }}</p>@enderror
                </div>
                <x-ui.button variant="danger" type="submit">Transfer ownership</x-ui.button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
