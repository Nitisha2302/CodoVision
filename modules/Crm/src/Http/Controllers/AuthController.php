<?php

namespace Codovision\Crm\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function logout(): RedirectResponse
    {
        Auth::guard('crm')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('crm.login');
    }
}
