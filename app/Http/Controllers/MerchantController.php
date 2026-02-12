<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\MerchantBank;
use App\Models\Echeck;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class MerchantController extends Controller
{
    public function showSignup()
    {
        return view('merchant.signup');
    }

    public function storeSignup(Request $request)
    {
        $data = Validator::make($request->all(), [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:merchants,email'],
            'password' => ['required','min:8','confirmed'],
        ])->validate();

        $merchant = Merchant::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'pending',
        ]);

        $request->session()->put('merchant_id', $merchant->id);

        return redirect()->route('merchant.bank.connect')
            ->with('success', 'Account created. Please verify your bank account via Plaid.');
    }

    public function showLogin()
    {
        return view('merchant.login');
    }

    public function login(Request $request)
    {
        $data = Validator::make($request->all(), [
            'email' => ['required','email'],
            'password' => ['required'],
        ])->validate();

        $merchant = Merchant::where('email', $data['email'])->first();
        if (!$merchant || !Hash::check($data['password'], $merchant->password)) {
            return back()->with('error', 'Invalid credentials.');
        }

        $request->session()->put('merchant_id', $merchant->id);
        return redirect()->route('merchant.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('merchant_id');
        return redirect()->route('merchant.login')->with('success', 'Logged out.');
    }

    // public function dashboard(Request $request)
    // {
    //     $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));
    //     $bank = $merchant->banks()->latest()->first();
    //     $transactions = $merchant->echeck()->latest()->get();
    //     $query = $merchant->echeck()->latest();
    //     $pagination = $query->paginate(10)->withQueryString();
    //     return view('merchant.dashboard', compact('merchant', 'bank', 'transactions', 'pagination'));
    // }

    // public function dashboard(Request $request)
    // {
    //     $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));
    //     $bank = $merchant->banks()->latest()->first();

    //     // Status filter from URL: ?status=all|pending|cleared|rejected
    //     $active = strtolower((string) $request->query('status', 'all'));
    //     if (!in_array($active, ['all','pending','cleared','rejected'], true)) {
    //         $active = 'all';
    //     }

    //     // Base query (IMPORTANT: keep your relation name echeck() if that's what you have)
    //     $query = $merchant->echeck()->latest();

    //     // Apply filter to query (so pagination works with filter)
    //     if ($active !== 'all') {
    //         $query->where('status', $active);
    //     }

    //     // ✅ Paginate only (10 per page)
    //     $pagination = $query->paginate(7)->withQueryString();

    //     // ✅ Counts must be from ALL records (not only current page)
    //     $statusCounts = [
    //         'all'      => $merchant->echeck()->count(),
    //         'pending'  => $merchant->echeck()->where('status', 'pending')->count(),
    //         'cleared'  => $merchant->echeck()->where('status', 'cleared')->count(),
    //         'rejected' => $merchant->echeck()->where('status', 'rejected')->count(),
    //     ];

    //     return view('merchant.dashboard', compact(
    //         'merchant',
    //         'bank',
    //         'pagination',
    //         'statusCounts',
    //         'active'
    //     ));
    // }

    public function dashboard(Request $request)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));
        $bank = $merchant->banks()->latest()->first();

        $isApproved = strtolower((string) ($merchant->status ?? 'pending')) === 'approved';
        $status = strtolower((string) $request->query('status', 'all'));

        // Base query (only this merchant)
        $query = $merchant->echeck()->latest();

        // Apply filter BEFORE paginate
        if (in_array($status, ['pending','cleared','rejected'], true)) {
            $query->where('status', $status);
        }

        // Paginate 10
        $pagination = $query->paginate(10)->withQueryString();

        // Counts for sidebar (no pagination)
        $statusCounts = [
            'all'      => $merchant->echeck()->count(),
            'pending'  => $merchant->echeck()->where('status', 'pending')->count(),
            'cleared'  => $merchant->echeck()->where('status', 'cleared')->count(),
            'rejected' => $merchant->echeck()->where('status', 'rejected')->count(),
        ];

        return view('merchant.dashboard', compact(
            'merchant',
            'bank',
            'pagination',
            'statusCounts',
            'status',
            'isApproved'
        ));
    }


    public function bankConnect(Request $request)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));
        return view('merchant.bank_connect', compact('merchant'));
    }

    public function createLinkToken(Request $request, PlaidService $plaid)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));

        $linkToken = $plaid->createLinkTokenForMerchant($merchant);

        return response()->json(['link_token' => $linkToken]);
    }

    /**
     * Exchange Plaid public_token, pull auth + identity, run verification rules, auto-approve merchant.
     */
    public function exchangePublicToken(Request $request, PlaidService $plaid)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));

        $data = Validator::make($request->all(), [
            'public_token' => ['required','string'],
            'account_id' => ['required','string'],
        ])->validate();

        $exchange = $plaid->exchangePublicToken($data['public_token']);
        $accessToken = $exchange['access_token'];
        $itemId = $exchange['item_id'];

        $auth = $plaid->authGet($accessToken);
        $identity = $plaid->identityGet($accessToken);

        $selected = $plaid->findAuthAccount($auth, $data['account_id']);
        if (!$selected) {
            return response()->json(['ok' => false, 'message' => 'Account not found in Plaid auth response.'], 422);
        }

        $verification = $plaid->verifyMerchantAutoApprove($merchant, $selected, $identity);

        // Save bank connection
        MerchantBank::updateOrCreate(
            ['merchant_id' => $merchant->id, 'plaid_account_id' => $data['account_id']],
            [
                'plaid_item_id' => $itemId,
                'plaid_access_token' => $accessToken,
                'plaid_account_id' => $data['account_id'],
                'name' => $selected['name'] ?? null,
                'mask' => $selected['mask'] ?? null,
                'account_type' => $selected['type'] ?? null,
                'account_subtype' => $selected['subtype'] ?? null,
                'routing_number' => $selected['routing'] ?? null,
                'account_number' => $selected['account'] ?? null,
                'verification_status' => $verification['status'],
                'verification_json' => $verification,
            ]
        );

        // Auto-approve if verified
        if ($verification['status'] === 'verified') {
            $merchant->update([
                'status' => 'approved',
                'verified_at' => now(),
            ]);
        } else {
            $merchant->update(['status' => 'rejected']);
        }

        return response()->json([
            'ok' => true,
            'merchant_status' => $merchant->status,
            'verification' => $verification,
        ]);
    }
    public function showManualBankForm(Request $request)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));
        return view('merchant.bank_manual', compact('merchant'));
    }

    public function storeManualBank(Request $request)
    {
        $merchant = Merchant::findOrFail($request->session()->get('merchant_id'));

        $data = Validator::make($request->all(), [
            'account_holder_name' => ['required','string','max:255'],
            'bank_name' => ['nullable','string','max:255'],
            'routing_number' => ['required','digits:9'],
            'account_number' => ['required','string','min:4','max:32'],
            'account_type' => ['required','in:checking,savings'],
        ])->validate();

        // Optional: very light routing check (ABA can't start with 0? Actually some do)
        // We'll just keep it simple and allow any 9 digits.

        MerchantBank::updateOrCreate(
            [
                'merchant_id' => $merchant->id,
                // If your table requires plaid_account_id unique, store a placeholder
                'plaid_account_id' => 'manual_'.$merchant->id,
            ],
            [
                'plaid_item_id' => null,
                'plaid_access_token' => null,
                'name' => $data['bank_name'] ?? 'Manual Bank',
                'mask' => substr($data['account_number'], -4),
                'account_type' => 'depository',
                'account_subtype' => $data['account_type'],
                'routing_number' => $data['routing_number'],
                'account_number' => $data['account_number'],
                'verification_status' => 'manual_pending',
                'verification_json' => [
                    'method' => 'manual',
                    'account_holder_name' => $data['account_holder_name'],
                    'bank_name' => $data['bank_name'] ?? null,
                    'status' => 'manual_pending',
                    'submitted_at' => now()->toISOString(),
                    'note' => 'Manual bank entry collected while Plaid is disabled.',
                ],
            ]
        );

        // Keep merchant pending until you later verify with Plaid (or admin review)
        $merchant->update([
            'status' => 'pending',
        ]);

        return redirect()->route('merchant.dashboard')
            ->with('success', 'Bank details saved for review. Verification is temporarily manual until Plaid is enabled.');
    }

    public function bulkPdf(Request $request)
    {
        $merchantId = $request->session()->get('merchant_id');

        $ids = $request->input('echecks', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one eCheck.');
        }

        $echecks = Echeck::where('merchant_id', $merchantId)
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get();

        if ($echecks->isEmpty()) {
            return back()->with('error', 'No valid eChecks selected.');
        }

        $pdf = Pdf::loadView('merchant.echeck.bulk-pdf', [
            'echecks' => $echecks,
        ])->setPaper('letter', 'portrait');

        return $pdf->download('echecks-bulk.pdf');
    }

}


