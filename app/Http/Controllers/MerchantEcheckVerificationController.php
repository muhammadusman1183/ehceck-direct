<?php

namespace App\Http\Controllers;

use App\Models\EcheckVerification;
use App\Models\Merchant;
use App\Services\PlaidService;
use App\Models\Echeck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MerchantEcheckVerificationController extends Controller
{
    public function index(Request $request)
    {
        $merchantId = $request->session()->get('merchant_id');
        $merchant = Merchant::findOrFail($merchantId);

        $recent = EcheckVerification::where('merchant_id', $merchant->id)
            ->latest()->limit(25)->get();

        // Sidebar counts (Paycron-like)
        // $statusCounts = [
        //     'all' => $recent->count(),
        //     'pending' => $recent->where('status','pending')->count(),
        //     'cleared' => $recent->where('status','cleared')->count(), // not used here but keep consistent
        //     'rejected' => $recent->where('status','rejected')->count(), // not used here but keep consistent
        // ];

        $statusCounts = [
            'all' => Echeck::where('merchant_id', $merchant->id)->count(),
            'pending' => Echeck::where('merchant_id', $merchant->id)->where('status','pending')->count(),
            'cleared' => Echeck::where('merchant_id', $merchant->id)->where('status','cleared')->count(),
            'rejected' => Echeck::where('merchant_id', $merchant->id)->where('status','rejected')->count(),
        ];

        return view('merchant.echeck.verify', [
            'merchant' => $merchant,
            'active' => 'verify',
            'statusCounts' => $statusCounts,
            'recent' => $recent,
        ]);
    }

    public function linkToken(Request $request, PlaidService $plaid)
    {
        $merchantId = $request->session()->get('merchant_id');
        $merchant = Merchant::findOrFail($merchantId);

        // Use PlaidService helper (customer-style token)
        // Ensure your PlaidService has createLinkTokenForCustomer($merchant)
        $linkToken = $plaid->createLinkTokenForCustomer($merchant);

        return response()->json(['link_token' => $linkToken]);
    }

    public function submit(Request $request, PlaidService $plaid)
    {
        $merchantId = $request->session()->get('merchant_id');
        $merchant = Merchant::findOrFail($merchantId);

        $data = Validator::make($request->all(), [
            'customer_name' => ['required','string','max:255'],
            'customer_email' => ['nullable','email','max:255'],
            'customer_phone' => ['nullable','string','max:40'],
            'amount' => ['required','numeric','min:0.01'],

            // From Plaid Link
            'public_token' => ['required','string'],
            'account_id' => ['required','string'],
        ])->validate();

        // Exchange token
        $exchange = $plaid->exchangePublicToken($data['public_token']);
        $accessToken = $exchange['access_token'] ?? null;
        $itemId = $exchange['item_id'] ?? null;

        // Auth (routing/account)
        $auth = $plaid->authGet($accessToken);
        $authAcct = $plaid->findAuthAccount($auth, $data['account_id']);
        $identity = $plaid->identityGet($accessToken);

        $owner = $plaid->getOwnerFromIdentity($identity, $merchantId);

        $owner = $plaid->getOwnerFromIdentity($identity, $data['account_id']);

        $ownerNames = $owner['name'] ?? [];

        if (is_string($ownerNames)) {
            $ownerNames = [$ownerNames];
        } elseif (!is_array($ownerNames)) {
            $ownerNames = [];
        }
        
        $ownerNames = array_values(array_filter($ownerNames, fn($n) => is_string($n) && trim($n) !== ''));
        
        if (!empty($ownerNames)) {
            $ok = $plaid->customerNameMatchesIdentity($data['customer_name'], $ownerNames);
            
            if (! $ok) {
                return back()
                    ->withInput()
                    ->with('error', 'Customer name does not match the bank account holder name. Please enter the exact account holder name.');
            }
        } else {
            /**
             * Identity names not available.
             *
             * Option A (recommended): Allow, but warn (soft-fail)
             * - Continue verification using Auth (routing/account) only.
             * - Store a note in your DB so you can review later.
             */
            session()->flash('warning', 'Bank owner name could not be retrieved from Plaid Identity. Verification proceeded without name match.');
        
            // Option B (strict): Block if Identity names are missing
            // return back()
            //     ->withInput()
            //     ->with('error', 'Unable to verify account holder name (Identity unavailable). Try another bank.');
        }
        
        if (!$authAcct || empty($authAcct['routing']) || empty($authAcct['account'])) {
            $rec = EcheckVerification::create([
                'merchant_id' => $merchant->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'amount' => $data['amount'],
                'plaid_item_id' => $itemId,
                'plaid_access_token' => $accessToken,
                'plaid_account_id' => $data['account_id'],
                'status' => 'failed',
                'result_json' => [
                    'ok' => false,
                    'reason' => 'Missing routing/account numbers from Plaid Auth.',
                ],
            ]);

            return redirect()->route('merchant.echeck.verify.index')
                ->with('error', 'Verification failed: routing/account missing.')
                ->with('last_verification_id', $rec->id);
        }

        // Balance (optional; may fail if product not enabled)
        $balanceInfo = null;
        $balanceError = null;

        try {
            $balanceResp = $plaid->balanceGet($accessToken);
            $balanceInfo = $plaid->findBalanceForAccount($balanceResp, $data['account_id']);
        } catch (\Throwable $e) {
            $balanceError = $e->getMessage();
        }

        // Decide verified/failed using Paycron-like rule:
        // If balance available is known: require available >= amount
        // If balance not available: pass auth-only (mark verified but with warning)
        $amount = (float)$data['amount'];

        $available = $balanceInfo['available'] ?? null;
        $current = $balanceInfo['current'] ?? null;

        $verified = true;
        $reasons = [];

        if ($available !== null) {
            if ((float)$available < $amount) {
                $verified = false;
                $reasons[] = 'Insufficient available balance for requested amount.';
            }
        } else {
            $reasons[] = 'Balance not available (Plaid Balance product not enabled). Verified using Auth only.';
        }

        $rec = EcheckVerification::create([
            'merchant_id' => $merchant->id,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'amount' => $amount,

            'plaid_item_id' => $itemId,
            'plaid_access_token' => $accessToken,
            'plaid_account_id' => $data['account_id'],

            'routing_number' => $authAcct['routing'] ?? null,
            'account_last4' => $authAcct['account'] ? substr($authAcct['account'], -4) : null,

            'available_balance' => $available,
            'current_balance' => $current,

            'status' => $verified ? 'verified' : 'failed',
            'result_json' => [
                'ok' => $verified,
                'auth_account' => [
                    'name' => $authAcct['name'] ?? null,
                    'subtype' => $authAcct['subtype'] ?? null,
                    'routing_last4' => $authAcct['routing'] ? substr($authAcct['routing'], -4) : null,
                    'account_last4' => $authAcct['account'] ? substr($authAcct['account'], -4) : null,
                ],
                'balance' => $balanceInfo,
                'balance_error' => $balanceError,
                'reasons' => $reasons,
                'checked_at' => now()->toISOString(),
                'reference' => 'ver_'.Str::lower(Str::random(12)),
            ],
        ]);

        return redirect()->route('merchant.echeck.verify.index')
            ->with($verified ? 'success' : 'error', $verified ? 'Bank verified successfully.' : 'Verification failed: insufficient funds.')
            ->with('last_verification_id', $rec->id);
    }
}
