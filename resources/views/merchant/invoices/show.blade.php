@extends('layouts.app')

@section('content')
<div class="max-w-4xl">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ $invoice->invoice_number }}</h1>
            <p class="text-slate-600 mt-1 text-sm">
                {{ $invoice->customer_name }} • ${{ number_format($invoice->amount_cents/100, 2) }} • Status: {{ $invoice->status }}
            </p>
        </div>
        <a href="{{ route('merchant.invoices.index') }}" class="text-sm underline">Back</a>
    </div>

    <div class="mt-6 grid lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="font-semibold">Payment Link</h2>
            <p class="text-sm text-slate-600 mt-1">Send this link to your customer:</p>

            <div class="mt-3 rounded-xl border bg-slate-50 p-3 text-sm overflow-auto">
                {{ $publicPayUrl }}
            </div>

            @if($invoice->notes)
                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-slate-700">Notes</h3>
                    <p class="text-sm text-slate-600 mt-1 whitespace-pre-line">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white border rounded-2xl shadow-sm p-6">
            <h2 class="font-semibold">Customer</h2>
            <div class="mt-2 text-sm text-slate-700 space-y-1">
                <div><span class="text-slate-500">Name:</span> {{ $invoice->customer_name }}</div>
                @if($invoice->customer_email)<div><span class="text-slate-500">Email:</span> {{ $invoice->customer_email }}</div>@endif
                @if($invoice->customer_phone)<div><span class="text-slate-500">Phone:</span> {{ $invoice->customer_phone }}</div>@endif
                @if($invoice->due_at)<div><span class="text-slate-500">Due:</span> {{ $invoice->due_at->toFormattedDateString() }}</div>@endif
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white border rounded-2xl shadow-sm">
        <div class="p-6 border-b">
            <h2 class="font-semibold">Payments</h2>
            <p class="text-sm text-slate-600 mt-1">Balance verification + status history.</p>
        </div>

        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left p-3">Reference</th>
                        <th class="text-left p-3">Balance Check</th>
                        <th class="text-left p-3">Status</th>
                        <th class="text-left p-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                    <tr class="border-t">
                        <td class="p-3 font-mono">{{ $p->reference }}</td>
                        <td class="p-3">{{ $p->balance_status }}</td>
                        <td class="p-3">{{ $p->status }}</td>
                        <td class="p-3 text-slate-600">{{ $p->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-slate-600">No payments yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
