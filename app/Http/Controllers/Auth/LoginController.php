<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $user = Auth::user();

        if ($user?->isActive() && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user?->isActive() && $user->isEmployee()) {
            return redirect()->route('employee.dashboard');
        }

        if ($user) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return $this->failed($request, 'Invalid login credentials.');
        }

        $request->session()->regenerate();

        if (! $request->user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->failed($request, 'Your account is inactive. Please contact admin.');
        }

        $target = $this->homeAfterLogin($request);

        // The login page submits via AJAX so it can play the unlock animation before navigating.
        if ($request->expectsJson()) {
            return response()->json(['redirect' => $target, 'name' => $request->user()->name]);
        }

        return redirect()->to($target);
    }

    private function failed(Request $request, string $message)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message, 'errors' => ['email' => [$message]]], 422)
            : back()->withErrors(['email' => $message])->onlyInput('email');
    }

    /**
     * Send the user back to the page they originally asked for, but only when it belongs
     * to their own area. Otherwise (e.g. an admin URL left in the session from a previous
     * user on this browser) go to their dashboard instead of a 403 page.
     */
    private function homeAfterLogin(Request $request): string
    {
        $isAdmin = $request->user()->isAdmin();
        $home = $isAdmin ? route('admin.dashboard') : route('employee.dashboard');
        $intended = $request->session()->pull('url.intended');
        $area = rtrim(url($isAdmin ? 'admin' : 'employee'), '/').'/';

        return is_string($intended) && str_starts_with($intended, $area) ? $intended : $home;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
