<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMediaCompanyApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->submissions()->where('status', 'disetujui')->exists()) {
            return redirect()
                ->route('media.dashboard')
                ->with('error', 'Fitur ini terkunci sampai pengajuan perusahaan disetujui admin.');
        }

        return $next($request);
    }
}
