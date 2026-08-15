<?php

namespace App\Http\Controllers;

use App\Support\DashboardNavigation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardNavigation $navigation): View
    {
        return view('dashboard', ['sections' => $navigation->forUser($request->user())]);
    }
}
