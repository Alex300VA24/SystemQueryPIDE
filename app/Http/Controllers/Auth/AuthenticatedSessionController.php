<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Livewire\Actions\Logout;
use Illuminate\Http\RedirectResponse;

final class AuthenticatedSessionController extends Controller
{
    public function destroy(Logout $logout): RedirectResponse
    {
        $logout();

        return redirect()->route('login');
    }
}
