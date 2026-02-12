@extends('layouts.app')

@section('content')
<style>
    /* Page wrapper */
    .bank-wrap{
        max-width: 860px;
        margin: 48px auto;
        padding: 0 16px;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
        color: #111827;
    }

    /* Card */
    .bank-card{
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(17,24,39,.06);
        overflow: hidden;
    }

    .bank-header{
        padding: 22px 22px 18px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
    }

    .bank-title{
        display:flex;
        gap:12px;
        align-items:flex-start;
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .bank-subtitle{
        margin: 8px 0 0;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.4;
    }

    .badge{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid #fde68a;
        background: #fffbeb;
        color: #92400e;
        white-space: nowrap;
    }
    .badge-dot{
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245,158,11,.20);
    }

    /* Alerts */
    .alert{
        border-radius: 12px;
        padding: 12px 14px;
        margin: 14px 22px 0;
        border: 1px solid transparent;
        font-size: 14px;
        line-height: 1.45;
    }
    .alert-danger{
        background:#fef2f2;
        border-color:#fecaca;
        color:#991b1b;
    }
    .alert-warn{
        background:#fffbeb;
        border-color:#fde68a;
        color:#92400e;
    }
    .alert ul{
        margin: 8px 0 0;
        padding-left: 18px;
    }

    /* Form */
    .bank-body{
        padding: 18px 22px 22px;
    }

    .grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    @media (max-width: 720px){
        .grid{ grid-template-columns: 1fr; }
    }

    .field{
        display:flex;
        flex-direction:column;
        gap: 6px;
    }

    .label{
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }

    .hint{
        font-size: 12px;
        color: #6b7280;
    }

    .input, .select{
        width: 100%;
        border: 1px solid #d1d5db;
        background: #ffffff;
        border-radius: 12px;
        padding: 11px 12px;
        font-size: 14px;
        outline: none;
        transition: box-shadow .15s ease, border-color .15s ease, transform .05s ease;
    }

    .input:focus, .select:focus{
        border-color: #93c5fd;
        box-shadow: 0 0 0 4px rgba(59,130,246,.12);
    }

    .row-span-2{ grid-column: span 2; }
    @media (max-width: 720px){
        .row-span-2{ grid-column: span 1; }
    }

    /* Footer actions */
    .bank-footer{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        padding: 16px 22px;
        border-top: 1px solid #e5e7eb;
        background:#fafafa;
    }

    .btn{
        border: 0;
        border-radius: 12px;
        padding: 12px 14px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        transition: transform .05s ease, filter .15s ease, box-shadow .15s ease;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap: 10px;
        text-decoration:none;
    }
    .btn:active{ transform: translateY(1px); }

    .btn-primary{
        background:#111827;
        color:#ffffff;
        box-shadow: 0 10px 18px rgba(17,24,39,.12);
        min-width: 220px;
    }
    .btn-primary:hover{ filter: brightness(1.05); }

    .btn-ghost{
        background: transparent;
        color:#111827;
        border: 1px solid #e5e7eb;
    }
    .btn-ghost:hover{ background:#ffffff; }

    .lock{
        width: 16px;
        height: 16px;
        display:inline-block;
        border-radius: 4px;
        background: rgba(255,255,255,.2);
        position: relative;
    }
    .lock:before{
        content:"";
        position:absolute;
        left:50%;
        top: 4px;
        transform: translateX(-50%);
        width: 10px;
        height: 8px;
        border-radius: 2px;
        background: rgba(255,255,255,.85);
    }
    .lock:after{
        content:"";
        position:absolute;
        left:50%;
        top:-1px;
        transform: translateX(-50%);
        width: 10px;
        height: 8px;
        border: 2px solid rgba(255,255,255,.85);
        border-bottom: 0;
        border-radius: 8px 8px 0 0;
        background: transparent;
    }

    .fineprint{
        font-size: 12px;
        color: #6b7280;
        line-height: 1.35;
    }
</style>

<div class="bank-wrap">

    <div class="bank-card">
        <div class="bank-header">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap;">
                <h2 class="bank-title">
                    Manual Bank Connection
                </h2>

                <span class="badge">
                    <span class="badge-dot"></span>
                    Temporary Mode
                </span>
            </div>

            <p class="bank-subtitle">
                Plaid is currently disabled. You can still add your bank details manually to continue setup.
                Your account will remain <strong>Pending</strong> until verification is enabled.
            </p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-warn">
            <strong>Note:</strong> For security, only enter bank details on a trusted device.
            (This is a temporary manual flow.)
        </div>

        <form method="POST" action="{{ route('merchant.bank.manual.store') }}">
            @csrf

            <div class="bank-body">
                <div class="grid">
                    <div class="field">
                        <label class="label" for="account_holder_name">Account Holder Name</label>
                        <input
                            id="account_holder_name"
                            class="input"
                            type="text"
                            name="account_holder_name"
                            value="{{ old('account_holder_name', $merchant->name) }}"
                            autocomplete="name"
                            required
                        >
                        <div class="hint">Name on the bank account.</div>
                    </div>

                    <div class="field">
                        <label class="label" for="bank_name">Bank Name <span class="hint">(optional)</span></label>
                        <input
                            id="bank_name"
                            class="input"
                            type="text"
                            name="bank_name"
                            value="{{ old('bank_name') }}"
                            autocomplete="organization"
                        >
                        <div class="hint">Example: Chase, Wells Fargo, Bank of America.</div>
                    </div>

                    <div class="field">
                        <label class="label" for="routing_number">Routing Number</label>
                        <input
                            id="routing_number"
                            class="input"
                            type="text"
                            name="routing_number"
                            value="{{ old('routing_number') }}"
                            maxlength="9"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="9 digits"
                            required
                        >
                        <div class="hint">US ABA routing number (9 digits).</div>
                    </div>

                    <div class="field">
                        <label class="label" for="account_number">Account Number</label>
                        <input
                            id="account_number"
                            class="input"
                            type="password"
                            name="account_number"
                            value="{{ old('account_number') }}"
                            autocomplete="off"
                            placeholder="Enter account number"
                            required
                        >
                        <div class="hint">We’ll display only the last 4 digits on your dashboard.</div>
                    </div>

                    <div class="field row-span-2">
                        <label class="label" for="account_type">Account Type</label>
                        <select id="account_type" name="account_type" class="select" required>
                            <option value="">Select account type...</option>
                            <option value="checking" @selected(old('account_type')==='checking')>Checking</option>
                            <option value="savings" @selected(old('account_type')==='savings')>Savings</option>
                        </select>
                        <div class="hint">Choose the account you want to receive ACH deposits into.</div>
                    </div>
                </div>
            </div>

            <div class="bank-footer">
                <div class="fineprint">
                    By saving, you agree your details may be reviewed for verification when Plaid is enabled.
                </div>

                <button type="submit" class="btn btn-primary">
                    <span class="lock"></span>
                    Save Bank Details
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
