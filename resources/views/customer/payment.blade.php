@extends('layouts.app')

@section('content')
<div class="max-w-2xl bg-white rounded-xl shadow-sm border p-6">
    <h1 class="text-xl font-semibold">Pay {{ $merchant->name }}</h1>
    <p class="text-slate-700 mt-1">Pay by eCheck using your bank account (Plaid).</p>

    <form class="mt-6 space-y-4" method="POST" action="{{ route('customer.payment.submit', $merchant) }}">
        @csrf

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium">Your Name</label>
                <input id="customer_name" class="mt-1 w-full rounded-lg border p-2" required />
            </div>
            <div>
                <label class="block text-sm font-medium">Your Email</label>
                <input id="customer_email" type="email" class="mt-1 w-full rounded-lg border p-2" required />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Amount (USD)</label>
            <input name="amount" type="number" step="0.01" min="1" class="mt-1 w-full rounded-lg border p-2" required />
        </div>

        <div>
            <label class="block text-sm font-medium">Memo (optional)</label>
            <input name="memo" class="mt-1 w-full rounded-lg border p-2" />
        </div>

        <input type="hidden" name="customer_id" id="customer_id" />

        <div class="rounded-xl border p-4">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="font-semibold">Bank Account</h2>
                    <p class="text-sm text-slate-700">Connect your checking account via Plaid.</p>
                </div>
                <button type="button" id="plaidCustomerButton" class="px-4 py-2 rounded-lg bg-slate-900 text-white">
                    Connect Bank
                </button>
            </div>
            <div id="bankStatus" class="mt-3 text-sm text-slate-700"></div>
        </div>

        <button class="w-full rounded-lg bg-emerald-600 text-white py-2 disabled:opacity-50" id="submitBtn" disabled>
            Submit Payment
        </button>
    </form>
</div>

<script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
<script>
async function post(url, data) {
  const res = await fetch(url, {
    method: 'POST',
    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
    body: JSON.stringify(data || {})
  });
  return await res.json();
}

const bankStatus = document.getElementById('bankStatus');
const submitBtn = document.getElementById('submitBtn');

document.getElementById('plaidCustomerButton').addEventListener('click', async () => {
  const name = document.getElementById('customer_name').value;
  const email = document.getElementById('customer_email').value;
  if (!name || !email) {
    bankStatus.textContent = 'Enter your name and email first.';
    return;
  }

  const { link_token } = await post('{{ route('customer.plaid.link_token', $merchant) }}', {});
  const handler = Plaid.create({
    token: link_token,
    onSuccess: async (public_token, metadata) => {
      const accountId = metadata.accounts?.[0]?.id;
      bankStatus.textContent = 'Saving bank connection...';

      const resp = await post('{{ route('customer.plaid.exchange', $merchant) }}', {
        public_token,
        account_id: accountId,
        customer_name: name,
        customer_email: email
      });

      if (resp.ok) {
        document.getElementById('customer_id').value = resp.customer_id;
        bankStatus.textContent = 'Bank connected ✔ You can now submit payment.';
        submitBtn.disabled = false;
      } else {
        bankStatus.textContent = 'Could not connect bank.';
      }
    }
  });
  handler.open();
});
</script>
@endsection
