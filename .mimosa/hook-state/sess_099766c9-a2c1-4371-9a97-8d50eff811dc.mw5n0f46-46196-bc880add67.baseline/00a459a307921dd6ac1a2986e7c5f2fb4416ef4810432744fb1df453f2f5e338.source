<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    /** Renders resources/docs/changelog/*.markdown, newest first. */
    public function index(): View
    {
        $dir = resource_path('docs/changelog');
        $entries = [];

        if (is_dir($dir)) {
            foreach (glob($dir.'/*.md') ?: [] as $file) {
                $entries[basename($file, '.md')] = file_get_contents($file) ?: '';
            }
            krsort($entries);
        }

        return view('public.changelog', [
            'entries' => collect($entries)->mapWithKeys(fn ($markdown, $version) => [
                $version => Str::markdown($markdown),
            ]),
        ]);
    }
}
