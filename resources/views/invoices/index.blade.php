@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto bg-white border rounded-xl p-6 shadow-sm">
    <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">Invoices</h1>
        <a href="{{ url('/merchant/invoices/create') }}"
           class="bg-slate-900 text-white px-4 py-2 rounded-lg text-sm">
            + Create Invoice
        </a>
    </div>

    <table class="w-full mt-6 text-sm border rounded-lg overflow-hidden">
        <thead class="bg-slate-50">
            <tr>
                <th class="p-3 text-left">Invoice</th>
                <th class="p-3 text-left">Customer</th>
                <th class="p-3 text-left">Amount</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Payment Link</th>
            </tr>
        </thead>
        <tbody>
        @forelse($invoices as $invoice)
            <tr class="border-t">
                <td class="p-3 font-mono">{{ $invoice->number }}</td>
                <td class="p-3">{{ $invoice->customer->name }}</td>
                <td class="p-3">${{ number_format($invoice->amount,2) }}</td>
                <td class="p-3">{{ ucfirst($invoice->status) }}</td>
                <td class="p-3">
                    <input readonly
                        class="w-full text-xs bg-slate-100 border rounded p-1"
                        value="{{ url('/pay/'.$invoice->uuid) }}">
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">
                    No invoices yet.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
