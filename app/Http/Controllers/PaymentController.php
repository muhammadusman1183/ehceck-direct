<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBank;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Services\PlaidService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function showPayment(Request $request, Merchant $merchant)
    {
        if ($merchant->status !== 'approved') {
            abort(404);
        }

        return view('customer.payment', compact('merchant'));
    }

    public function createCustomerLinkToken(Request $request, Merchant $merchant, PlaidService $plaid)
    {
        if ($merchant->status !== 'approved') {
            return response()->json(['message' => 'Merchant not available.'], 404);
        }

        $linkToken = $plaid->createLinkTokenForCustomer($merchant);

        return response()->json(['link_token' => $linkToken]);
    }

    public function exchangeCustomerPublicToken(Request $request, Merchant $merchant, PlaidService $plaid)
    {
        if ($merchant->status !== 'approved') {
            return response()->json(['message' => 'Merchant not available.'], 404);
        }

        $data = Validator::make($request->all(), [
            'public_token' => ['required','string'],
            'account_id' => ['required','string'],
            'customer_name' => ['required','string','max:255'],
            'customer_email' => ['required','email','max:255'],
        ])->validate();

        $exchange = $plaid->exchangePublicToken($data['public_token']);
        $accessToken = $exchange['access_token'];
        $itemId = $exchange['item_id'];

        $auth = $plaid->authGet($accessToken);
        $selected = $plaid->findAuthAccount($auth, $data['account_id']);
        if (!$selected) {
            return response()->json(['ok' => false, 'message' => 'Account not found.'], 422);
        }

        $customer = Customer::firstOrCreate(
            ['email' => $data['customer_email']],
            ['name' => $data['customer_name']]
        );

        CustomerBank::updateOrCreate(
            ['customer_id' => $customer->id, 'plaid_account_id' => $data['account_id']],
            [
                'plaid_item_id' => $itemId,
                'plaid_access_token' => $accessToken,
                'name' => $selected['name'] ?? null,
                'mask' => $selected['mask'] ?? null,
                'account_type' => $selected['type'] ?? null,
                'account_subtype' => $selected['subtype'] ?? null,
                'routing_number' => $selected['routing'] ?? null,
                'account_number' => $selected['account'] ?? null,
            ]
        );

        return response()->json(['ok' => true, 'customer_id' => $customer->id]);
    }

    /**
     * NOTE: This skeleton records a transaction. To actually move money, integrate an ACH processor (e.g., Dwolla).
     */
    public function submitPayment(Request $request, Merchant $merchant)
    {
        if ($merchant->status !== 'approved') {
            abort(404);
        }

        $data = Validator::make($request->all(), [
            'customer_id' => ['required','integer','exists:customers,id'],
            'amount' => ['required','numeric','min:1'],
            'memo' => ['nullable','string','max:255'],
        ])->validate();

        $tx = Transaction::create([
            'merchant_id' => $merchant->id,
            'customer_id' => $data['customer_id'],
            'amount' => $data['amount'],
            'currency' => 'USD',
            'status' => 'pending',
            'reference' => 'TX-' . strtoupper(Str::random(10)),
            'memo' => $data['memo'] ?? null,
        ]);

        // In real ACH flow:
        // - create transfer with your processor
        // - store processor transfer id
        // - update status via webhooks

        return redirect()->route('customer.payment', $merchant)->with('success', 'Payment submitted (recorded as pending). Ref: '.$tx->reference);
    }
}
