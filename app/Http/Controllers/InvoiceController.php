<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $merchantId = $request->session()->get('merchant_id');
        $invoices = Invoice::where('merchant_id',$merchantId)->latest()->get();
        return view('merchant.invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::all();
        return view('merchant.invoices.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'=>'required',
            'amount'=>'required|numeric|min:1',
            'memo'=>'nullable'
        ]);

        Invoice::create([
            'merchant_id'=>session('merchant_id'),
            'customer_id'=>$data['customer_id'],
            'amount'=>$data['amount'],
            'memo'=>$data['memo']
        ]);

        return redirect()->route('merchant.invoices')->with('success','Invoice created');
    }
}
