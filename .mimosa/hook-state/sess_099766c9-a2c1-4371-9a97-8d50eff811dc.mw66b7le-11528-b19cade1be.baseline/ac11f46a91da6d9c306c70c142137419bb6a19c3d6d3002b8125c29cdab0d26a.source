<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard.support', [
            'tickets' => $request->user()->tickets()->latest('updated_at')->paginate(15),
        ]);
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        return view('dashboard.support-show', [
            'ticket' => $ticket->load('messages.user'),
        ]);
    }

    public function store(Request $request, SupportTicketService $service): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:general,billing,technical,feature'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $workspace = $request->user()->currentWorkspace();

        $ticket = $service->create(
            $request->user(),
            $workspace ?? $request->user()->workspaces()->firstOrFail(),
            $validated['subject'],
            $validated['category'],
            $validated['body'],
        );

        return redirect()->route('dashboard.support.show', $ticket)
            ->with('success', "Ticket {$ticket->number} created.");
    }

    public function reply(Request $request, SupportTicket $ticket, SupportTicketService $service): RedirectResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        try {
            $service->reply($ticket, $request->user(), $validated['body'], isStaff: false);
        } catch (\RuntimeException $e) {
            return redirect()->route('dashboard.support.show', $ticket)->with('error', $e->getMessage());
        }

        return redirect()->route('dashboard.support.show', $ticket);
    }
}
