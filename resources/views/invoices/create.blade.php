@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white border rounded-xl p-6 shadow-sm">
    <h1 class="text-xl font-semibold mb-4">Create Invoice</h1>

    <form method="POST" action="{{ url('/merchant/invoices') }}">
        @csrf

        <div class="mb-3">
            <label class="block text-sm font-medium">Customer</label>
            <select name="customer_id" class="w-full border rounded-lg p-2" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Amount ($)</label>
            <input name="amount" type="number" step="0.01"
                   class="w-full border rounded-lg p-2" required>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium">Memo (optional)</label>
            <textarea name="memo" class="w-full border rounded-lg p-2"></textarea>
        </div>

        <button class="w-full bg-slate-900 text-white py-2 rounded-lg">
            Create Invoice
        </button>
    </form>
</div>
@endsection
