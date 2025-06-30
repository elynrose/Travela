<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();

            if (!$user->is_admin) {
                return redirect('/')->with('error', 'Unauthorized access.');
            }

            return $next($request);
        } catch (\Exception $e) {
            return redirect('/')->with('error', 'An error occurred.');
        }
    }
}
