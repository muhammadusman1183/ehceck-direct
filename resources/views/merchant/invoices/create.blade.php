@extends('layouts.app')

@section('content')
<div class="max-w-2xl">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-semibold">New Invoice</h1>
            <p class="text-slate-600 mt-1 text-sm">Enter customer details and amount.</p>
        </div>
        <a href="{{ route('merchant.invoices.index') }}" class="text-sm underline">Back</a>
    </div>

    <div class="mt-6 bg-white border rounded-2xl shadow-sm p-6">
        <form method="POST" action="{{ route('merchant.invoices.store') }}" class="space-y-4">
            @csrf

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Customer Name</label>
                    <input name="customer_name" value="{{ old('customer_name') }}"
                           class="mt-1 w-full rounded-lg border p-2" required>
                    @error('customer_name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Amount (USD)</label>
                    <input name="amount" type="number" step="0.01" min="1" value="{{ old('amount') }}"
                           class="mt-1 w-full rounded-lg border p-2" required>
                    @error('amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium">Customer Email (optional)</label>
                    <input name="customer_email" type="email" value="{{ old('customer_email') }}"
                           class="mt-1 w-full rounded-lg border p-2">
                    @error('customer_email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium">Customer Phone (optional)</label>
                    <input name="customer_phone" value="{{ old('customer_phone') }}"
                           class="mt-1 w-full rounded-lg border p-2">
                    @error('customer_phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium">Due Date (optional)</label>
                <input name="due_at" type="date" value="{{ old('due_at') }}"
                       class="mt-1 w-full rounded-lg border p-2">
                @error('due_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium">Notes (optional)</label>
                <textarea name="notes" rows="4"
                          class="mt-1 w-full rounded-lg border p-2">{{ old('notes') }}</textarea>
                @error('notes')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <button class="w-full rounded-lg bg-slate-900 text-white py-2 font-medium">
                Create Invoice
            </button>
        </form>
    </div>
</div>
@endsection
