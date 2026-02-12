<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pay Invoice</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
</head>
<body class="bg-slate-50 text-slate-900">
<div class="max-w-xl mx-auto p-4 md:p-8">
    <div class="bg-white border rounded-2xl shadow-sm p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs uppercase tracking-wide text-slate-500">Invoice</div>
                <h1 class="text-2xl font-semibold mt-1">{{ $invoice->invoice_number }}</h1>
                <p class="text-slate-600 mt-1 text-sm">
                    Pay <span class="font-medium">${{ number_format($invoice->amount_cents/100, 2) }}</span>
                    to <span class="font-medium">{{ $invoice->merchant->name }}</span>
                </p>
            </div>
        </div>

        <div class="mt-5 rounded-xl border bg-slate-50 p-4 text-sm text-slate-700">
            To confirm your payment, we’ll connect your bank and verify your available funds.
        </div>

        <div class="mt-5 space-y-3">
            <button id="btnConnect"
                    class="w-full rounded-lg bg-slate-900 text-white py-2 font-medium">
                Connect Bank & Verify Funds
            </button>

            <div id="result" class="hidden rounded-xl border p-4 text-sm"></div>
        </div>

        <p class="text-xs text-slate-500 mt-5">
            Note: This checks balance availability. Actual ACH debit requires payment rails (e.g., Transfer/processor).
        </p>
    </div>
</div>

<script>
(async function () {
    const btn = document.getElementById('btnConnect');
    const result = document.getElementById('result');

    function showResult(ok, msg) {
        result.classList.remove('hidden');
        result.classList.toggle('border-emerald-200', ok);
        result.classList.toggle('bg-emerald-50', ok);
        result.classList.toggle('text-emerald-900', ok);

        result.classList.toggle('border-red-200', !ok);
        result.classList.toggle('bg-red-50', !ok);
        result.classList.toggle('text-red-900', !ok);

        result.textContent = msg;
    }

    async function getLinkToken() {
        const res = await fetch(@json(route('customer.invoice.link_token', $invoice->public_token)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({})
        });
        const data = await res.json();
        return data.link_token;
    }

    btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = 'Opening bank connection...';

        try {
            const linkToken = await getLinkToken();

            const handler = Plaid.create({
                token: linkToken,
                onSuccess: async (public_token, metadata) => {
                    const account_id = metadata.accounts?.[0]?.id || metadata.account_id;
                    if (!account_id) {
                        showResult(false, 'No account selected.');
                        btn.disabled = false;
                        btn.textContent = 'Connect Bank & Verify Funds';
                        return;
                    }

                    const res = await fetch(@json(route('customer.invoice.submit', $invoice->public_token)), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @json(csrf_token())
                        },
                        body: JSON.stringify({ public_token, account_id })
                    });

                    const out = await res.json();
                    if (out.ok && out.balance_status === 'sufficient') {
                        showResult(true, 'Funds verified ✅ (Payment marked pending for collection)');
                    } else if (out.ok && out.balance_status === 'insufficient') {
                        showResult(false, 'Insufficient funds ❌');
                    } else {
                        showResult(false, out.reason || 'Unable to verify funds right now.');
                    }

                    btn.disabled = false;
                    btn.textContent = 'Connect Bank & Verify Funds';
                },
                onExit: () => {
                    btn.disabled = false;
                    btn.textContent = 'Connect Bank & Verify Funds';
                }
            });

            handler.open();
        } catch (e) {
            showResult(false, 'Error: ' + (e?.message || 'Failed to open Plaid.'));
            btn.disabled = false;
            btn.textContent = 'Connect Bank & Verify Funds';
        }
    });
})();
</script>
</body>
</html>
