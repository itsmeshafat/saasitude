<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function home(): View
    {
        $plans = Plan::with('prices')->where('is_public', true)->where('is_archived', false)->orderBy('id')->limit(3)->get();

        return view('public.home', ['plans' => $plans]);
    }

    public function pricing(): View
    {
        $plans = Plan::with(['prices', 'planFeatures.feature'])
            ->where('is_public', true)
            ->where('is_archived', false)
            ->orderBy('id')
            ->get();

        return view('public.pricing', ['plans' => $plans]);
    }
}
