@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white border rounded-xl p-6 shadow-sm">

    <h1 class="text-xl font-semibold mb-2">
        Pay Invoice {{ $invoice->number }}
    </h1>

    <p class="text-slate-700 mb-4">
        Amount due: <strong>${{ number_format($invoice->amount,2) }}</strong>
    </p>

    {{-- Step 1: Bank Entry --}}
    @if(!session('verify') && !session('verified'))
        <form method="POST" action="{{ url('/pay/'.$invoice->uuid.'/bank') }}">
            @csrf

            <h2 class="font-medium mb-2">Enter Bank Details</h2>

            <input name="account_holder_name" class="w-full border p-2 mb-2 rounded"
                   placeholder="Account holder name" required>

            <input name="routing_number" class="w-full border p-2 mb-2 rounded"
                   placeholder="Routing number" required>

            <input name="account_number" class="w-full border p-2 mb-2 rounded"
                   placeholder="Account number" required>

            <select name="account_type" class="w-full border p-2 mb-4 rounded" required>
                <option value="checking">Checking</option>
                <option value="savings">Savings</option>
            </select>

            <button class="w-full bg-slate-900 text-white py-2 rounded-lg">
                Continue
            </button>
        </form>
    @endif

    {{-- Step 2: Verify micro deposits --}}
    @if(session('verify') && !session('verified'))
        <form method="POST" action="{{ url('/pay/'.$invoice->uuid.'/verify') }}">
            @csrf

            <h2 class="font-medium mb-2">Verify Bank Account</h2>
            <p class="text-sm text-slate-600 mb-2">
                Enter the two small deposit amounts (in cents).
            </p>

            <input name="a" class="w-full border p-2 mb-2 rounded"
                   placeholder="Amount 1 (cents)" required>

            <input name="b" class="w-full border p-2 mb-4 rounded"
                   placeholder="Amount 2 (cents)" required>

            <button class="w-full bg-slate-900 text-white py-2 rounded-lg">
                Verify Bank
            </button>
        </form>
    @endif

    {{-- Step 3: Submit payment --}}
    @if(session('verified'))
        <form method="POST" action="{{ url('/pay/'.$invoice->uuid.'/submit') }}">
            @csrf

            <div class="bg-green-50 border border-green-200 p-3 rounded mb-3">
                Bank verified successfully.
            </div>

            <button class="w-full bg-green-600 text-white py-2 rounded-lg">
                Submit Payment
            </button>
        </form>
    @endif

</div>
@endsection
