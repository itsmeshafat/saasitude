<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use HasinHayder\TyroDashboard\Services\AdminNotice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSupportInboxController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = SupportTicket::query()
            ->with(['user', 'workspace'])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();

        return view('dashboard.support-inbox', [
            'tickets' => $tickets,
            'filterStatus' => (string) $request->query('status', ''),
            'statusCounts' => SupportTicket::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
        ]);
    }

    public function show(SupportTicket $ticket): View
    {
        return view('dashboard.support-inbox-show', ['ticket' => $ticket->load('messages.user', 'workspace')]);
    }

    public function reply(Request $request, SupportTicket $ticket, SupportTicketService $service): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('support.reply'), 403);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        try {
            $service->reply($ticket, $request->user(), $validated['body'], isStaff: true);
        } catch (\RuntimeException $e) {
            return redirect()->route('admin.support.show', $ticket)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.support.show', $ticket);
    }

    public function close(Request $request, SupportTicket $ticket, SupportTicketService $service): RedirectResponse
    {
        abort_unless($request->user()->hasPrivilege('support.reply'), 403);

        $service->close($ticket);

        AdminNotice::show("Ticket {$ticket->number} closed.", '#2563eb', '#ffffff', 'center');

        return redirect()->route('admin.support.index')->with('success', 'Ticket closed.');
    }
}
