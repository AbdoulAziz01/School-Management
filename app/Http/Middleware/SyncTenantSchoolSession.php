<?php

namespace App\Http\Middleware;

use App\Support\TenantSchool;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SyncTenantSchoolSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! $request->session()->has(TenantSchool::FILTER_KEY)) {
            TenantSchool::applyForUser(Auth::user());
        }

        if (! Auth::check() && $request->session()->has(TenantSchool::FILTER_KEY)) {
            TenantSchool::clear();
        }

        return $next($request);
    }
}
