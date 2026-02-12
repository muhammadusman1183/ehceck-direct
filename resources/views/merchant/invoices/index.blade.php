@extends('layouts.app')

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold">Invoices</h1>
        <p class="text-slate-600 mt-1 text-sm">Create invoices and share payment links with customers.</p>
    </div>
    <a href="{{ route('merchant.invoices.create') }}"
       class="rounded-lg bg-slate-900 text-white px-4 py-2 text-sm font-medium">
        + New Invoice
    </a>
</div>

<div class="mt-6 overflow-auto border rounded-2xl bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="text-left p-3">Invoice</th>
                <th class="text-left p-3">Customer</th>
                <th class="text-left p-3">Amount</th>
                <th class="text-left p-3">Status</th>
                <th class="text-left p-3">Created</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
        @forelse($invoices as $inv)
            <tr class="border-t">
                <td class="p-3 font-medium">{{ $inv->invoice_number }}</td>
                <td class="p-3">{{ $inv->customer_name }}</td>
                <td class="p-3">${{ number_format($inv->amount_cents/100, 2) }}</td>
                <td class="p-3">
                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs border">
                        {{ $inv->status }}
                    </span>
                </td>
                <td class="p-3 text-slate-600">{{ $inv->created_at->diffForHumans() }}</td>
                <td class="p-3 text-right">
                    <a class="text-slate-900 underline" href="{{ route('merchant.invoices.show', $inv) }}">View</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-slate-600">No invoices yet.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $invoices->links() }}
</div>
@endsection
