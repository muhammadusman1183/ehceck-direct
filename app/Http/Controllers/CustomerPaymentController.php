<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\CustomerBank;
use App\Models\MicroDeposit;
use App\Models\Transaction;
use Illuminate\Http\Request;

class CustomerPaymentController extends Controller
{
    public function show($uuid)
    {
        $invoice = Invoice::where('uuid',$uuid)->firstOrFail();
        return view('customer.pay', compact('invoice'));
    }

    public function saveBank(Request $request, $uuid)
    {
        $invoice = Invoice::where('uuid',$uuid)->firstOrFail();

        $bank = CustomerBank::create([
            'customer_id'=>$invoice->customer_id,
            'account_holder_name'=>$request->account_holder_name,
            'routing_number'=>$request->routing_number,
            'account_number_last4'=>substr($request->account_number,-4),
            'account_type'=>$request->account_type,
            'verification_status'=>'pending'
        ]);

        foreach ([rand(1,99), rand(1,99)] as $cents) {
            MicroDeposit::create([
                'customer_bank_id'=>$bank->id,
                'amount_cents'=>$cents,
                'expires_at'=>now()->addDays(7)
            ]);
        }

        return redirect()->back()->with('verify', true);
    }

    public function verify(Request $request, $uuid)
    {
        $invoice = Invoice::where('uuid',$uuid)->firstOrFail();
        $bank = CustomerBank::where('customer_id',$invoice->customer_id)->latest()->first();

        $amounts = collect([$request->a, $request->b])->sort()->values();
        $expected = MicroDeposit::where('customer_bank_id',$bank->id)
            ->pluck('amount_cents')->sort()->values();

        if ($amounts == $expected) {
            $bank->update(['verification_status'=>'verified']);
            return back()->with('verified',true);
        }

        return back()->withErrors(['verification'=>'Amounts did not match']);
    }

    public function submit(Request $request, $uuid)
    {
        $invoice = Invoice::where('uuid',$uuid)->firstOrFail();

        Transaction::create([
            'merchant_id'=>$invoice->merchant_id,
            'customer_id'=>$invoice->customer_id,
            'amount'=>$invoice->amount,
            'status'=>'pending',
            'reference'=>'TX'.rand(100000,999999),
        ]);

        $invoice->update(['status'=>'paid']);

        return redirect()->back()->with('success','Payment submitted');
    }
}
