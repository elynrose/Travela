<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }

    public function terms()
    {
        $page = Page::where('slug', 'terms')->first();
        if (!$page) {
            abort(404);
        }
        return view('pages.terms', compact('page'));
    }

    public function privacy()
    {
        $page = Page::where('slug', 'privacy')->first();
        if (!$page) {
            abort(404);
        }
        return view('pages.privacy', compact('page'));
    }

    public function cookies()
    {
        $page = Page::where('slug', 'cookies')->first();
        if (!$page) {
            abort(404);
        }
        return view('pages.cookies', compact('page'));
    }
} 