@extends('layouts.app')
<style>
    .dash-wrap {
        max-width: 1200px;
        margin: 28px auto;
        padding: 0 14px;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
        color: #0f172a;
    }

    .shell {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 16px;
        align-items: start;
    }

    @media (max-width: 980px) {
        .shell {
            grid-template-columns: 1fr;
        }
    }

    .card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 12px 26px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    /* Sidebar */
    .sidebar {
        position: sticky;
        top: 14px;
    }

    @media (max-width: 980px) {
        .sidebar {
            position: static;
        }
    }

    .side-head {
        padding: 18px 18px 14px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .brand {
        font-weight: 900;
        letter-spacing: -.02em;
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .brand-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #111827;
        box-shadow: 0 0 0 4px rgba(17, 24, 39, .10);
    }

    .merchant-mini {
        margin-top: 10px;
        font-size: 13px;
        color: #64748b;
        line-height: 1.35;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid #e5e7eb;
        background: #fff;
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 10px;
    }

    .pill-dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #94a3b8;
    }

    .pill-dot.approved {
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, .12);
    }

    .pill-dot.pending {
        background: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, .12);
    }

    .pill-dot.rejected {
        background: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .12);
    }

    .side-body {
        padding: 12px;
    }

    .nav {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .nav a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        text-decoration: none;
        color: #0f172a;
        border: 1px solid transparent;
        background: #fff;
        transition: background .15s ease, border-color .15s ease, transform .05s ease;
        font-weight: 800;
        font-size: 13px;
    }

    .nav a:hover {
        background: #f8fafc;
        border-color: #e5e7eb;
    }

    .nav a.active {
        background: #111827;
        color: #fff;
        border-color: #111827;
        box-shadow: 0 12px 18px rgba(17, 24, 39, .12);
    }

    .count {
        display: inline-flex;
        min-width: 28px;
        height: 22px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        background: #f1f5f9;
        color: #0f172a;
        padding: 0 8px;
    }

    .nav a.active .count {
        background: rgba(255, 255, 255, .18);
        color: #fff;
    }

    .side-foot {
        padding: 12px 16px 16px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
    }

    .btn {
        border: 0;
        border-radius: 12px;
        padding: 10px 12px;
        font-weight: 900;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform .05s ease, filter .15s ease;
        white-space: nowrap;
    }

    .btn:active {
        transform: translateY(1px);
    }

    .btn-ghost {
        background: transparent;
        border: 1px solid #e5e7eb;
        color: #0f172a;
    }

    .btn-ghost:hover {
        background: #fff;
    }

    .btn-dark {
        background: #111827;
        color: #fff;
        box-shadow: 0 12px 18px rgba(17, 24, 39, .12);
    }

    .btn-dark:hover {
        filter: brightness(1.05);
    }

    /* Main header */
    .main-head {
        padding: 18px 18px 14px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 140%);
    }

    .hrow {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .title {
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        letter-spacing: -.02em;
    }

    .subtitle {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 13px;
        line-height: 1.35;
    }

    .right-actions {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Alerts + tiles */
    .alert {
        margin: 14px 18px 0;
        border-radius: 14px;
        padding: 12px 14px;
        border: 1px solid transparent;
        font-size: 13px;
        line-height: 1.45;
    }

    .alert-warn {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .main-body {
        padding: 16px 18px 18px;
    }

    .tiles {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }

    @media (max-width: 980px) {
        .tiles {
            grid-template-columns: 1fr;
        }
    }

    .tile {
        grid-column: span 4;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        padding: 14px 14px 12px;
    }

    @media (max-width: 980px) {
        .tile {
            grid-column: span 12;
        }
    }

    .tile .k {
        font-size: 12px;
        color: #64748b;
        font-weight: 800;
    }

    .tile .v {
        margin-top: 6px;
        font-size: 18px;
        font-weight: 950;
    }

    .tile .s {
        margin-top: 6px;
        font-size: 12px;
        color: #64748b;
        line-height: 1.35;
    }

    .mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    }

    /* Table */
    .table-wrap {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    thead {
        background: #f8fafc;
    }

    th,
    td {
        text-align: left;
        padding: 11px 12px;
    }

    tbody tr {
        border-top: 1px solid #eef2f7;
    }

    tbody tr:hover {
        background: #fafafa;
    }

    .status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #0f172a;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #94a3b8;
    }

    .dot.pending {
        background: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, .12);
    }

    .dot.cleared {
        background: #22c55e;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, .12);
    }

    .dot.rejected {
        background: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .12);
    }

    .copybox {
        display: flex;
        gap: 10px;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #f8fafc;
        padding: 10px 12px;
        overflow: auto;
    }

    .copybox .text {
        font-size: 12px;
        color: #0f172a;
        white-space: nowrap;
    }

    .mini {
        font-size: 12px;
        color: #64748b;
        line-height: 1.35;
    }
</style>
@section('content')
@php
    $active = 'verify'; // or 'echecks' depending how you mark active
@endphp

{{-- Reuse your dashboard CSS (best: move into a shared CSS file or layout once) --}}


<div class="dash-wrap">
    <div class="shell">

        {{-- Sidebar (same everywhere) --}}
        @include('merchant.partials.sidebar', [
            'merchant' => $merchant,
            'active' => 'verify',
            'statusCounts' => $statusCounts ?? ['all'=>0,'pending'=>0,'cleared'=>0,'rejected'=>0],
        ])

        <main class="card">
            <div class="main-head">
                <div class="hrow">
                    <div>
                        <h1 class="title">Create eCheck</h1>
                        <p class="subtitle">Enter customer details, connect bank with Plaid, then create the eCheck.</p>
                    </div>

                    <div class="right-actions">
                        <a class="btn btn-ghost" href="{{ route('merchant.dashboard') }}">Back to Dashboard</a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert" style="background:#ecfdf5;border-color:#bbf7d0;color:#065f46;margin:14px 18px 0;">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-warn">{{ session('error') }}</div>
            @endif

            <div class="main-body">

                {{-- CREATE FORM --}}
                <div id="verifyCard" class="tile" style="grid-column: span 12; margin-bottom: 14px;">
                    <div class="k">New eCheck</div>
                    <div class="s" style="margin-top:8px;">
                        Step 1: Fill customer + amount. Step 2: Connect bank. Step 3: Submit.
                    </div>

                    <form id="echeckForm" class="mt-4" method="POST" action="{{ route('merchant.echeck.store') }}">
                        @csrf

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px;">
                            <div>
                                <label class="mini" style="display:block;margin-bottom:6px;"><strong>Customer Name</strong></label>
                                <input name="customer_name" value="{{ old('customer_name') }}"
                                       style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:12px;" required>
                                @error('customer_name')<div class="mini" style="color:#b91c1c;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="mini" style="display:block;margin-bottom:6px;"><strong>Amount</strong></label>
                                <input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount') }}"
                                       style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:12px;" required>
                                @error('amount')<div class="mini" style="color:#b91c1c;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="mini" style="display:block;margin-bottom:6px;"><strong>Customer Email (optional)</strong></label>
                                <input name="customer_email" type="email" value="{{ old('customer_email') }}"
                                       style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:12px;">
                                @error('customer_email')<div class="mini" style="color:#b91c1c;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label class="mini" style="display:block;margin-bottom:6px;"><strong>Customer Phone (optional)</strong></label>
                                <input name="customer_phone" value="{{ old('customer_phone') }}"
                                       style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:12px;">
                                @error('customer_phone')<div class="mini" style="color:#b91c1c;margin-top:6px;">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Plaid Link outputs --}}
                        <input type="hidden" name="public_token" id="public_token" value="">
                        <input type="hidden" name="account_id" id="account_id" value="">

                        <div style="display:flex;gap:10px;align-items:center;margin-top:14px;flex-wrap:wrap;">
                            <button type="button" class="btn btn-dark" id="connectPlaidBtn">Connect Bank</button>
                            <button type="submit" class="btn btn-ghost" id="submitBtn" disabled>Create eCheck</button>

                            <div class="mini" id="plaidStatus" style="margin-left:auto;">
                                Not connected.
                            </div>
                        </div>

                        <div class="mini" style="margin-top:10px;">
                            Note: This creates an eCheck record using Plaid Auth verification. Actual ACH debit comes later via an ACH provider.
                        </div>
                    </form>
                </div>

                {{-- RECENT --}}
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Bank</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($recent as $r)
                            @php
                                $st = strtolower($r->status ?? 'pending');
                                $dot = $st === 'cleared' ? 'cleared' : ($st === 'rejected' ? 'rejected' : 'pending');
                            @endphp
                            <tr>
                                <td>{{ $r->customer_name }}</td>
                                <td>${{ number_format((float)$r->amount, 2) }}</td>
                                <td>
                                    <span class="status">
                                        <span class="dot {{ $dot }}"></span>
                                        {{ ucfirst($r->status) }}
                                    </span>
                                </td>
                                <td class="mono">
                                    @if($r->routing_number)
                                        RT ****{{ substr($r->routing_number, -4) }} • AC ****{{ $r->account_last4 }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ optional($r->created_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding:18px;color:#64748b;">No eChecks yet.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </main>
    </div>
</div>

{{-- Plaid Link --}}
<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
<script>
(async function(){
    const btn = document.getElementById('connectPlaidBtn');
    const status = document.getElementById('plaidStatus');
    const submitBtn = document.getElementById('submitBtn');

    async function getLinkToken(){
        const res = await fetch("{{ route('merchant.echeck.link_token') }}", {
            method: "POST",
            headers: {
                "Content-Type":"application/json",
                "X-CSRF-TOKEN":"{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        });
        const data = await res.json();
        if(!data.link_token) throw new Error(data.message || "No link_token returned");
        return data.link_token;
    }

    btn.addEventListener('click', async function(){
        btn.disabled = true;
        status.textContent = "Opening Plaid…";

        try{
            const linkToken = await getLinkToken();

            const handler = Plaid.create({
                token: linkToken,
                onSuccess: function(public_token, metadata) {
                    const accountId =
                        (metadata.accounts && metadata.accounts[0] && metadata.accounts[0].id)
                            ? metadata.accounts[0].id
                            : "";

                    document.getElementById('public_token').value = public_token;
                    document.getElementById('account_id').value = accountId;

                    submitBtn.disabled = !accountId;
                    status.textContent = accountId
                        ? "Bank connected. Ready to create eCheck."
                        : "Connected, but no account selected.";

                    btn.disabled = false;
                },
                onExit: function(err) {
                    btn.disabled = false;
                    status.textContent = err ? ("Plaid exited: " + (err.display_message || err.error_message)) : "Plaid closed.";
                }
            });

            handler.open();
        } catch(e){
            btn.disabled = false;
            status.textContent = "Error: " + e.message;
        }
    });
})();
</script>
@endsection
