<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class MerchantInvoiceController extends Controller
{
    private function merchantId(Request $request): int
    {
        return (int) $request->session()->get('merchant_id');
    }

    public function index(Request $request)
    {
        $merchantId = $this->merchantId($request);

        $invoices = Invoice::where('merchant_id', $merchantId)
            ->latest()
            ->paginate(20);

        return view('merchant.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        return view('merchant.invoices.create');
    }

    public function store(Request $request)
    {
        $merchantId = $this->merchantId($request);

        $data = Validator::make($request->all(), [
            'customer_name' => ['required','string','max:255'],
            'customer_email' => ['nullable','email','max:255'],
            'customer_phone' => ['nullable','string','max:50'],
            'amount' => ['required','numeric','min:1'],
            'due_at' => ['nullable','date'],
            'notes' => ['nullable','string','max:5000'],
        ])->validate();

        $invoiceNumber = 'INV-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));

        $invoice = Invoice::create([
            'merchant_id' => $merchantId,
            'invoice_number' => $invoiceNumber,
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'amount_cents' => (int) round(((float) $data['amount']) * 100),
            'currency' => 'USD',
            'status' => 'sent',
            'due_at' => $data['due_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'public_token' => Str::uuid()->toString(),
        ]);

        return redirect()
            ->route('merchant.invoices.show', $invoice)
            ->with('success', 'Invoice created.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $merchantId = $this->merchantId($request);
        abort_unless($invoice->merchant_id === $merchantId, 403);

        $publicPayUrl = route('customer.invoice.pay', $invoice->public_token);

        $payments = $invoice->payments()->latest()->get();

        return view('merchant.invoices.show', compact('invoice','publicPayUrl','payments'));
    }
}
