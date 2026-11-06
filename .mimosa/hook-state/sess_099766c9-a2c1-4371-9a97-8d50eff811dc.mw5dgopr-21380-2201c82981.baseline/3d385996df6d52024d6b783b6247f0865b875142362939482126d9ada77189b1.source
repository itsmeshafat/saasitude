<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('public.contact');
    }

    /** Rate-limited; lands in the admin support inbox. */
    public function submit(Request $request, SupportTicketService $tickets): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:general,billing,technical,feature'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticket = $tickets->createGuest(
            $validated['email'],
            $validated['subject'],
            $validated['category'],
            $validated['body'],
        );

        return redirect()->route('public.contact')
            ->with('success', "Thanks! Your message is ticket {$ticket->number} — we'll reply to {$validated['email']} shortly.");
    }
}
