<?php

namespace App\Livewire\Actions;

use App\Services\Pide\PideCredentialStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        app(PideCredentialStore::class)->forget();

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
