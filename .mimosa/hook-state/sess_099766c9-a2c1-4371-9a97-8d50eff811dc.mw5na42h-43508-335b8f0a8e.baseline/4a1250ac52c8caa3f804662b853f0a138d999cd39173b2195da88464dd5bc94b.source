<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LegalController extends Controller
{
    /** @var array<string, string> page => [heading, LAWYER REVIEW note] */
    private const PAGES = [
        'terms' => 'Terms of Service',
        'privacy' => 'Privacy Policy',
        'refund-policy' => 'Refund Policy',
    ];

    public function show(string $page): View
    {
        abort_unless(isset(self::PAGES[$page]), 404);

        return view('public.legal', [
            'page' => $page,
            'heading' => self::PAGES[$page],
        ]);
    }
}
