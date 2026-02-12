@extends('layouts.app')

@section('content')
<div class="flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold">eChecks</h1>
        <p class="text-slate-600 mt-1 text-sm">
            @if($status) Filter: <span class="font-medium">{{ $status }}</span> @else All payments @endif
        </p>
    </div>
</div>

<div class="mt-6 overflow-auto border rounded-2xl bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="text-left p-3">Reference</th>
                <th class="text-left p-3">Invoice</th>
                <th class="text-left p-3">Amount</th>
                <th class="text-left p-3">Balance</th>
                <th class="text-left p-3">Status</th>
                <th class="text-left p-3">Created</th>
            </tr>
        </thead>
        <tbody>
        @forelse($payments as $p)
            <tr class="border-t">
                <td class="p-3 font-mono">{{ $p->reference }}</td>
                <td class="p-3">{{ $p->invoice->invoice_number }}</td>
                <td class="p-3">${{ number_format($p->amount_cents/100, 2) }}</td>
                <td class="p-3">{{ $p->balance_status }}</td>
                <td class="p-3">{{ $p->status }}</td>
                <td class="p-3 text-slate-600">{{ $p->created_at->diffForHumans() }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-6 text-slate-600">No eChecks found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $payments->links() }}
</div>
@endsection
