<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function plaid(Request $request)
    {
        // Optional: store and process Plaid webhooks if you enable them later.
        return response()->json(['ok' => true]);
    }
}
