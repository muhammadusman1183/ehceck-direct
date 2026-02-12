<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MerchantAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->has('merchant_id')) {
            return redirect()->route('merchant.login')->with('error', 'Please log in.');
        }
        return $next($request);
    }
}
