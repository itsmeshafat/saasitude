<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SupportTicketService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function create(User $user, Workspace $workspace, string $subject, string $category, string $body): SupportTicket
    {
        return DB::transaction(function () use ($user, $workspace, $subject, $category, $body) {
            $ticket = SupportTicket::create([
                'number' => $this->nextNumber(),
                'user_id' => $user->id,
                'workspace_id' => $workspace->id,
                'subject' => $subject,
                'category' => $category,
            ]);

            $ticket->messages()->create([
                'user_id' => $user->id,
                'is_staff' => false,
                'body' => $body,
            ]);

            return $ticket;
        });
    }

    /** Contact-form ticket from an anonymous visitor (public site). */
    public function createGuest(string $email, string $subject, string $category, string $body): SupportTicket
    {
        return DB::transaction(function () use ($email, $subject, $category, $body) {
            $ticket = SupportTicket::create([
                'number' => $this->nextNumber(),
                'guest_email' => $email,
                'subject' => $subject,
                'category' => $category,
            ]);

            $ticket->messages()->create([
                'user_id' => null,
                'is_staff' => false,
                'body' => $body,
            ]);

            return $ticket;
        });
    }

    public function reply(SupportTicket $ticket, User $author, string $body, bool $isStaff): TicketMessage
    {
        if ($ticket->status === 'closed') {
            throw new RuntimeException('This ticket is closed.');
        }

        $message = $ticket->messages()->create([
            'user_id' => $author->id,
            'is_staff' => $isStaff,
            'body' => $body,
        ]);

        // Notify the other side.
        if ($isStaff) {
            $this->notifications->send(
                $ticket->user,
                'support.reply',
                "Re: {$ticket->subject}",
                ['body' => Str::limit($body, 120)],
                route('dashboard.support.show', $ticket),
                $ticket->workspace,
            );
        } else {
            $ticket->forceFill(['status' => 'pending'])->save();
        }

        return $message;
    }

    public function close(SupportTicket $ticket): void
    {
        $ticket->close();

        $this->notifications->send(
            $ticket->user,
            'support.closed',
            "Ticket {$ticket->number} closed",
            ['body' => 'Your support request has been closed. Reply to reopen by creating a new ticket.'],
            route('dashboard.support.show', $ticket),
            $ticket->workspace,
        );
    }

    private function nextNumber(): string
    {
        // Same lock-for-update counter approach as invoice numbering.
        return DB::transaction(function () {
            $row = DB::table('settings')->where('key', 'system.ticket_counter')->lockForUpdate()->first();

            $next = ((int) ($row->value ?? 0)) + 1;
            $year = now()->year;

            if ($row) {
                DB::table('settings')->where('key', 'system.ticket_counter')->update(['value' => $next]);
            } else {
                DB::table('settings')->insert(['key' => 'system.ticket_counter', 'value' => $next, 'created_at' => now(), 'updated_at' => now()]);
            }

            return sprintf('TKT-%d-%05d', $year, $next);
        });
    }
}
