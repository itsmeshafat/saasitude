@extends('tyro-dashboard::layouts.user')

@section('title', 'Team')

@section('breadcrumb')
<a href="{{ route($dashboardRoute::name('index')) }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
<span>Team</span>
@endsection

@section('content')
@php
    $myMembership = $workspace->workspaceMembers->firstWhere('user_id', $user->id);
    $canManage = $myMembership && $myMembership->isAdmin();
@endphp

<div class="page-header">
    <div class="page-header-row">
        <div>
            <h1 class="page-title">Team</h1>
            <p class="page-description">Members of {{ $workspace->name }}.</p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h3 class="card-title">Members ({{ $members->count() }})</h3></div>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Joined</th>
                    @if($canManage)<th></th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($members as $member)
                    <tr>
                        <td>
                            <div class="user-cell">
                                <div class="user-cell-avatar">{{ substr($member->user?->name ?? '?', 0, 1) }}</div>
                                <div>
                                    <div class="font-medium">{{ $member->user?->name ?? '—' }}</div>
                                    <div class="text-sm text-[var(--muted-foreground)]">{{ $member->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <x-ui.badge variant="{{ $member->isOwner() ? 'primary' : 'secondary' }}">{{ ucfirst($member->role) }}</x-ui.badge>
                        </td>
                        <td>{{ optional($member->joined_at ?? $member->created_at)->format('M j, Y') }}</td>
                        @if($canManage)
                            <td class="text-right">
                                @unless($member->isOwner() || $member->user_id === $user->id)
                                    <form method="POST" action="{{ route('dashboard.team.remove', $member) }}"
                                          x-data @submit.prevent="showDanger('Remove member?', '{{ $member->user?->email }} will lose access to this workspace.', () => $el.submit())">
                                        @csrf
                                        <x-ui.button variant="danger" size="sm" type="submit">Remove</x-ui.button>
                                    </form>
                                @endunless
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($canManage)
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="card">
        <div class="card-header"><h3 class="card-title">Invite a member</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.team.invite') }}">
                @csrf
                <x-ui.input type="email" name="email" id="email" value="{{ old('email') }}"
                            label="Email" required :error="$errors->first('email')" />

                <div class="form-group">
                    <x-ui.select name="role" id="role" label="Role" required
                                 :options="['member' => 'Member', 'admin' => 'Admin', 'billing' => 'Billing']"
                                 :error="$errors->first('role')" />
                </div>
                <x-ui.button variant="primary" type="submit">Send invitation</x-ui.button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Pending invitations ({{ $invitations->count() }})</h3></div>
        @if($invitations->isEmpty())
            <div class="card-body"><x-ui.empty-state title="No pending invitations" description="Invited teammates will appear here until they accept." /></div>
        @else
            <div class="table-container">
                <table class="table table-compact">
                    <tbody>
                        @foreach($invitations as $invitation)
                            <tr>
                                <td>
                                    <span class="font-medium">{{ $invitation->email }}</span>
                                    <div class="text-xs text-[var(--muted-foreground)]">
                                        {{ ucfirst($invitation->role) }} · expires {{ $invitation->expires_at?->format('M j, Y') }}
                                    </div>
                                </td>
                                <td class="text-right"><x-ui.badge variant="warning">Pending</x-ui.badge></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endif
@endsection
