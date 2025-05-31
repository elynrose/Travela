<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            Log::info('AdminMiddleware: Starting check');
            
            if (!Auth::check()) {
                Log::info('AdminMiddleware: User not authenticated');
                return redirect()->route('login');
            }

            $user = Auth::user();
            Log::info('AdminMiddleware: User found', [
                'id' => $user->id,
                'email' => $user->email,
                'is_admin' => $user->is_admin
            ]);

            if (!$user->is_admin) {
                Log::info('AdminMiddleware: User is not admin');
                return redirect('/')->with('error', 'Unauthorized access.');
            }

            Log::info('AdminMiddleware: User is admin, proceeding');
            return $next($request);
        } catch (\Exception $e) {
            Log::error('AdminMiddleware: Error occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect('/')->with('error', 'An error occurred.');
        }
    }
}
