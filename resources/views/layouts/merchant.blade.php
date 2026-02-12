@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-12 gap-6">

        {{-- SIDEBAR --}}
        <aside class="col-span-12 md:col-span-3 lg:col-span-2">
            <div class="bg-white border rounded-2xl shadow-sm p-4 sticky top-6">
                <div class="mb-4">
                    <div class="text-xs text-slate-500">Merchant</div>
                    <div class="font-semibold text-slate-900">{{ $merchant->name ?? 'Dashboard' }}</div>
                </div>

                <nav class="space-y-1 text-sm">
                    @php
                        $current = request()->path();
                        $is = fn($path) => str_starts_with($current, trim($path,'/'));
                        $link = "flex items-center gap-2 px-3 py-2 rounded-xl transition";
                        $active = "bg-slate-900 text-white";
                        $idle = "text-slate-700 hover:bg-slate-50";
                    @endphp

                    <a class="{{ $link }} {{ $is('merchant/dashboard') ? $active : $idle }}"
                       href="{{ url('/merchant/dashboard') }}">
                        Dashboard
                    </a>

                    <div class="pt-3 mt-3 border-t">
                        <div class="text-xs font-semibold text-slate-500 px-3 mb-2">eChecks</div>

                        <a class="{{ $link }} {{ $is('merchant/payments') && request('status')==null ? $active : $idle }}"
                           href="{{ url('/merchant/payments') }}">
                            All Payments
                        </a>

                        <a class="{{ $link }} {{ request('status')==='pending' ? $active : $idle }}"
                           href="{{ url('/merchant/payments?status=pending') }}">
                            Pending eChecks
                        </a>

                        <a class="{{ $link }} {{ request('status')==='cleared' ? $active : $idle }}"
                           href="{{ url('/merchant/payments?status=cleared') }}">
                            Cleared eChecks
                        </a>

                        <a class="{{ $link }} {{ request('status')==='rejected' ? $active : $idle }}"
                           href="{{ url('/merchant/payments?status=rejected') }}">
                            Rejected eChecks
                        </a>
                    </div>

                    <div class="pt-3 mt-3 border-t">
                        <div class="text-xs font-semibold text-slate-500 px-3 mb-2">Invoices</div>

                        <a class="{{ $link }} {{ $is('merchant/invoices') ? $active : $idle }}"
                           href="{{ url('/merchant/invoices') }}">
                            Invoices
                        </a>

                        <a class="{{ $link }} {{ $is('merchant/invoices/create') ? $active : $idle }}"
                           href="{{ url('/merchant/invoices/create') }}">
                            Create Invoice
                        </a>
                    </div>

                    <div class="pt-3 mt-3 border-t">
                        <form method="POST" action="{{ route('merchant.logout') }}">
                            @csrf
                            <button class="w-full text-left px-3 py-2 rounded-xl text-red-600 hover:bg-red-50">
                                Logout
                            </button>
                        </form>
                    </div>
                </nav>
            </div>
        </aside>

        {{-- MAIN --}}
        <main class="col-span-12 md:col-span-9 lg:col-span-10">
            @yield('merchant_content')
        </main>

    </div>
</div>
@endsection
