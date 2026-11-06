<?php

namespace App\Jobs;

use App\Mail\DataExportReady;
use App\Models\DataExport;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProcessDataExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $exportId) {}

    public function handle(NotificationService $notifications): void
    {
        $export = DataExport::with('user')->find($this->exportId);

        if (! $export || $export->status !== 'pending') {
            return;
        }

        try {
            $payload = $this->gather($export->user);

            $path = "data-exports/{$export->user->id}/".uniqid('export-').'.json';
            Storage::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            $export->update(['file_path' => $path, 'status' => 'ready']);
        } catch (\Throwable $e) {
            $export->update(['status' => 'failed']);
            report($e);

            return;
        }

        Mail::to($export->user->email)->queue(new DataExportReady($export->user));
        $notifications->send(
            $export->user,
            'data.export',
            'Your data export is ready',
            ['body' => 'Download it from Data & Privacy — the link expires in 7 days.'],
            route('dashboard.data-privacy'),
        );
    }

    /** Everything we hold for this user, as portable JSON. */
    private function gather(User $user): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'account' => [
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'email_verified_at' => $user->email_verified_at?->toIso8601String(),
            ],
            'workspaces' => $user->workspaces()->with(['workspaceMembers.user:id,name,email', 'subscriptions.plan:id,name', 'invitations'])->get()->map(function ($w) {
                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'role' => $w->pivot?->role,
                    'members' => $w->workspaceMembers->map(fn ($m) => ['name' => $m->user?->name, 'email' => $m->user?->email, 'role' => $m->role]),
                    'subscriptions' => $w->subscriptions->map(fn ($s) => ['plan' => $s->plan?->name, 'status' => $s->status, 'created_at' => $s->created_at?->toIso8601String()]),
                    'invitations' => $w->invitations->map(fn ($i) => ['email' => $i->email, 'role' => $i->role, 'created_at' => $i->created_at?->toIso8601String()]),
                ];
            })->all(),
            'payments' => Payment::whereHas('workspace', fn ($q) => $q->where('owner_id', $user->id))->get(['gateway', 'amount', 'currency', 'status', 'created_at'])->all(),
            'invoices' => Invoice::whereHas('workspace', fn ($q) => $q->where('owner_id', $user->id))->get(['number', 'amount', 'currency', 'status', 'issued_at'])->all(),
        ];
    }
}
