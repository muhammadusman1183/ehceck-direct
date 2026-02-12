<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
<div class="min-h-screen flex">

    <aside class="w-64 hidden md:flex flex-col border-r bg-white">
        <div class="p-5 border-b">
            <div class="font-semibold text-lg">{{ config('app.name') }}</div>
            <div class="text-xs text-slate-500 mt-1">Merchant Portal</div>
        </div>

        <nav class="p-3 space-y-1 text-sm">
            <a href="{{ route('merchant.dashboard') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('merchant.invoices.index') }}"
               class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50">
                <span>Invoices</span>
            </a>

            <div class="pt-2">
                <div class="px-3 text-xs uppercase tracking-wide text-slate-500 mb-2">eChecks</div>

                <a href="{{ route('merchant.echecks.index') }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50">
                    <span>All</span>
                </a>

                <a href="{{ route('merchant.echecks.index', ['status' => 'pending']) }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50">
                    <span>Pending</span>
                </a>

                <a href="{{ route('merchant.echecks.index', ['status' => 'cleared']) }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50">
                    <span>Cleared</span>
                </a>

                <a href="{{ route('merchant.echecks.index', ['status' => 'rejected']) }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-50">
                    <span>Rejected</span>
                </a>
            </div>
        </nav>

        <div class="mt-auto p-4 border-t">
            <form method="POST" action="{{ route('merchant.logout') }}">
                @csrf
                <button class="w-full rounded-lg bg-slate-900 text-white py-2 text-sm">Logout</button>
            </form>
        </div>
    </aside>

    <main class="flex-1">
        <div class="max-w-6xl mx-auto p-4 md:p-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-emerald-900">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</div>
</body>
</html>
