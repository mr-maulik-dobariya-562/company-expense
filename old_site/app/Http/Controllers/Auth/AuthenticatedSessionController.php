<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $log = [];
        $log['subject'] = 'Login from the system';
        $log['url'] = FacadesRequest::fullUrl();
        $log['method'] = FacadesRequest::method();
        $log['ip'] = FacadesRequest::ip();
        $log['agent'] = FacadesRequest::header('user-agent');
        $log['user_id'] = auth()->check() ? auth()->user()->id : 1;

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $log = [];
        $log['subject'] = 'Logout from the system';
        $log['url'] = FacadesRequest::fullUrl();
        $log['method'] = FacadesRequest::method();
        $log['ip'] = FacadesRequest::ip();
        $log['agent'] = FacadesRequest::header('user-agent');
        $log['user_id'] = auth()->check() ? auth()->user()->id : 1;

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
