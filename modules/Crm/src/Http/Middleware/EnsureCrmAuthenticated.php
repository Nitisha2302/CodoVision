<?php

namespace Codovision\Crm\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('crm')->check()) {
            return redirect()->route('crm.login');
        }

        Auth::shouldUse('crm');

        $user = Auth::guard('crm')->user();
        if (!$user || !$user->is_active) {
            Auth::guard('crm')->logout();

            return redirect()->route('crm.login')->withErrors([
                'email' => 'Your CRM account is inactive.',
            ]);
        }

        return $next($request);
    }
}
