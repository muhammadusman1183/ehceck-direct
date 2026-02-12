<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CustomerPayment;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CustomerInvoicePaymentController extends Controller
{
    public function payPage(string $token)
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();

        return view('customer.invoice_pay', compact('invoice'));
    }

    public function createLinkToken(Request $request, string $token, PlaidService $plaid)
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();

        // Customer connects bank (Auth + Balance is typical)
        $body = [
            'client_name' => $invoice->merchant->name.' Payments',
            'language' => 'en',
            'country_codes' => [env('PLAID_ALLOWED_COUNTRY', 'US')],
            'user' => ['client_user_id' => 'invoice_'.$invoice->id.'_'.(string) Str::uuid()],
            'products' => ['auth','balance'],
            'redirect_uri' => config('services.plaid.redirect_uri', env('PLAID_REDIRECT_URI')),
        ];

        // Reuse PlaidService::post() by adding a public helper OR just add a method.
        // If you want minimal edits: add this method to PlaidService: createLinkTokenGeneric($body)
        $linkToken = $plaid->createLinkTokenGeneric($body);

        return response()->json(['link_token' => $linkToken]);
    }

    public function submit(Request $request, string $token, PlaidService $plaid)
    {
        $invoice = Invoice::where('public_token', $token)->firstOrFail();

        $data = Validator::make($request->all(), [
            'public_token' => ['required','string'],
            'account_id' => ['required','string'],
        ])->validate();

        $exchange = $plaid->exchangePublicToken($data['public_token']);
        $accessToken = $exchange['access_token'];
        $itemId = $exchange['item_id'];

        // Pull balance
        $balance = $plaid->balanceGet($accessToken);

        // Find the account’s available/current
        $account = collect($balance['accounts'] ?? [])->firstWhere('account_id', $data['account_id']);
        $available = $account['balances']['available'] ?? null;
        $current = $account['balances']['current'] ?? null;

        $needed = $invoice->amount_cents / 100;

        $status = 'unavailable';
        $reason = null;

        $best = $available ?? $current;

        if ($best === null) {
            $status = 'unavailable';
            $reason = 'Balance not returned by institution.';
        } elseif ((float) $best < (float) $needed) {
            $status = 'insufficient';
            $reason = 'Insufficient funds per balance check.';
        } else {
            $status = 'sufficient';
        }

        $payment = CustomerPayment::create([
            'invoice_id' => $invoice->id,
            'merchant_id' => $invoice->merchant_id,
            'reference' => 'PAY-'.strtoupper(Str::random(10)),

            'plaid_item_id' => $itemId,
            'plaid_access_token' => $accessToken,
            'plaid_account_id' => $data['account_id'],

            'balance_status' => $status,
            'balance_json' => $balance,

            // For now we only “verify”; actual ACH debit requires Transfer or an ACH processor.
            'status' => $status === 'sufficient' ? 'pending' : 'rejected',
            'decision_reason' => $reason,

            'amount_cents' => $invoice->amount_cents,
            'currency' => $invoice->currency,
        ]);

        return response()->json([
            'ok' => true,
            'payment_id' => $payment->id,
            'balance_status' => $status,
            'reason' => $reason,
            'next' => $status === 'sufficient'
                ? 'verified_for_collection'
                : 'cannot_collect',
        ]);
    }
}
