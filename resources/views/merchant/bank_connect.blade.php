@extends('layouts.app')

@section('content')
<div class="bg-white rounded-xl shadow-sm border p-6">
    <div class="flex items-start justify-between gap-6">
        <div>
            <h1 class="text-xl font-semibold">Connect your bank (Plaid)</h1>
            <p class="text-slate-700 mt-1">
                We use Plaid to verify your checking account. After verification, your merchant account is auto-approved.
            </p>
            <p class="text-slate-700 mt-2 text-sm">
                Status: <span class="font-medium">{{ $merchant->status }}</span>
            </p>
        </div>
        <form method="POST" action="{{ route('merchant.logout') }}">
            @csrf
            <button class="text-sm underline">Logout</button>
        </form>
    </div>

    <div class="mt-6">
        <button id="plaidButton" class="px-4 py-2 rounded-lg bg-slate-900 text-white">Verify with Plaid</button>
        <a href="{{ route('merchant.bank.manual') }}">Use Manual Bank Form (Temporary)</a>
        <div id="result" class="mt-4 text-sm"></div>
    </div>
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

document.getElementById('plaidButton').addEventListener('click', async () => {
  const { link_token } = await post('{{ route('merchant.plaid.link_token') }}', {});
  const handler = Plaid.create({
    token: link_token,
    onSuccess: async (public_token, metadata) => {
      // Merchant selects account; metadata.accounts[0].id is typical.
      const accountId = metadata.accounts?.[0]?.id;
      const out = document.getElementById('result');
      out.textContent = 'Exchanging token...';

      const resp = await post('{{ route('merchant.plaid.exchange') }}', {
        public_token,
        account_id: accountId
      });

      out.innerHTML = '<pre class="bg-slate-50 border rounded-lg p-3 overflow-auto">'+JSON.stringify(resp, null, 2)+'</pre>';

      if (resp.merchant_status === 'approved') {
        window.location.href = '{{ route('merchant.dashboard') }}';
      }
    }
  });
  handler.open();
});
</script>
@endsection
