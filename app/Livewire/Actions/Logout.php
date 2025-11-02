<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke()
    {
        $user = Auth::user();

        // Activity log: user logout
        try {
            if ($user) {
                activity('auth')
                    ->causedBy($user)
                    ->event('logout')
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ])
                    ->log('User logout');
            }
        } catch (\Throwable $e) {
            // ignore logging failure
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();

        return redirect('/');
    }
}
