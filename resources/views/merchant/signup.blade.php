@extends('layouts.app')

@section('content')
<style>
    .auth-wrap{
        max-width: 1100px;
        margin: 36px auto;
        padding: 0 14px;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji","Segoe UI Emoji";
        color:#0f172a;
    }
    .auth-shell{
        display:grid;
        grid-template-columns: 1.05fr .95fr;
        gap: 16px;
        align-items: stretch;
    }
    @media (max-width: 980px){
        .auth-shell{ grid-template-columns: 1fr; }
    }
    .card{
        background:#fff;
        border:1px solid #e5e7eb;
        border-radius: 18px;
        box-shadow: 0 14px 30px rgba(15,23,42,.08);
        overflow:hidden;
    }
    .hero{
        padding: 22px;
        background: radial-gradient(1200px 600px at 30% 10%, rgba(15,23,42,.10), transparent 55%),
                    linear-gradient(180deg, #0f172a 0%, #111827 60%, #0b1220 100%);
        color:#fff;
        position:relative;
    }
    .hero-inner{
        padding: 18px;
        border: 1px solid rgba(255,255,255,.14);
        border-radius: 18px;
        background: rgba(255,255,255,.06);
        backdrop-filter: blur(10px);
    }
    .brand{
        display:flex;
        align-items:center;
        gap:10px;
        font-weight: 900;
        letter-spacing: -.02em;
    }
    .brand-dot{
        width:10px;height:10px;border-radius:999px;background:#fff;
        box-shadow:0 0 0 4px rgba(255,255,255,.18);
    }
    .hero h1{
        margin: 14px 0 6px;
        font-size: 24px;
        font-weight: 950;
        letter-spacing: -.03em;
        line-height:1.15;
    }
    .hero p{
        margin: 0;
        color: rgba(255,255,255,.80);
        font-size: 13px;
        line-height: 1.5;
    }
    .hero-bullets{
        margin-top: 14px;
        display:grid;
        gap: 10px;
    }
    .bullet{
        display:flex;
        gap: 10px;
        align-items:flex-start;
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.14);
        background: rgba(255,255,255,.06);
    }
    .bullet .ic{
        width: 28px; height: 28px; border-radius: 10px;
        display:flex; align-items:center; justify-content:center;
        background: rgba(255,255,255,.14);
        font-weight: 900;
    }
    .bullet .txt{
        font-size: 12px;
        color: rgba(255,255,255,.82);
        line-height: 1.45;
    }

    .form{
        padding: 18px;
    }
    .form-head{
        padding: 18px 18px 14px;
        border-bottom: 1px solid #e5e7eb;
        background: linear-gradient(180deg,#ffffff 0%, #f8fafc 150%);
    }
    .form-title{
        margin: 0;
        font-size: 18px;
        font-weight: 950;
        letter-spacing: -.02em;
    }
    .form-sub{
        margin: 6px 0 0;
        color:#64748b;
        font-size: 13px;
        line-height: 1.45;
    }

    .alert{
        margin: 12px 18px 0;
        border-radius: 14px;
        padding: 12px 14px;
        border: 1px solid transparent;
        font-size: 13px;
        line-height: 1.45;
    }
    .alert-error{ background:#fef2f2; border-color:#fecaca; color:#7f1d1d; }

    .field{ margin-top: 12px; }
    .label{
        display:block;
        font-size: 12px;
        font-weight: 900;
        color:#0f172a;
        margin-bottom: 6px;
    }
    .input{
        width:100%;
        padding: 12px 12px;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        outline: none;
        background:#fff;
        font-size: 14px;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .input:focus{
        border-color:#111827;
        box-shadow: 0 0 0 4px rgba(17,24,39,.10);
    }
    .hint{
        margin-top: 8px;
        font-size: 12px;
        color:#64748b;
        line-height: 1.4;
    }

    .btn{
        width:100%;
        padding: 12px 14px;
        border-radius: 14px;
        border: 0;
        cursor:pointer;
        font-weight: 950;
        font-size: 14px;
        background:#111827;
        color:#fff;
        box-shadow: 0 14px 20px rgba(17,24,39,.14);
        transition: transform .05s ease, filter .15s ease;
        margin-top: 14px;
    }
    .btn:hover{ filter: brightness(1.06); }
    .btn:active{ transform: translateY(1px); }

    .row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin-top: 12px;
        flex-wrap: wrap;
    }
    .link{
        font-size: 12px;
        font-weight: 900;
        color:#111827;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .mini{
        font-size: 12px;
        color:#64748b;
        line-height: 1.4;
    }

    .req{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        font-size: 11px;
        font-weight: 900;
        color:#0f172a;
        background:#f1f5f9;
        border:1px solid #e5e7eb;
        padding: 2px 8px;
        border-radius: 999px;
        margin-left: 8px;
    }
</style>

<div class="auth-wrap">
    <div class="auth-shell">

        {{-- Left: branded panel --}}
        <section class="card hero">
            <div class="hero-inner">
                <div class="brand">
                    <span class="brand-dot"></span>
                    Merchant Portal
                </div>

                <h1>Create your merchant account</h1>
                <p>
                    Set up your business profile and start accepting customer eCheck payments.
                    Bank verification can be done manually while Plaid is disabled.
                </p>

                <div class="hero-bullets">
                    <div class="bullet">
                        <div class="ic">1</div>
                        <div class="txt"><strong style="color:#fff;">Create account</strong> with your business name and login.</div>
                    </div>
                    <div class="bullet">
                        <div class="ic">2</div>
                        <div class="txt"><strong style="color:#fff;">Connect bank</strong> (manual temporary) to begin onboarding.</div>
                    </div>
                    <div class="bullet">
                        <div class="ic">3</div>
                        <div class="txt"><strong style="color:#fff;">Share link</strong> with customers and track payments in your dashboard.</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Right: signup form --}}
        <section class="card">
            <div class="form-head">
                <h2 class="form-title">Merchant Signup</h2>
                <p class="form-sub">Create your login to access the merchant dashboard.</p>
            </div>

            @if(session('error'))
                <div class="alert alert-error">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0; padding-left: 18px;">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form">
                <form method="POST" action="{{ route('merchant.signup.store') }}" autocomplete="on">
                    @csrf

                    <div class="field">
                        <label class="label" for="name">
                            Business / Merchant Name <span class="req">Required</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            class="input"
                            placeholder="Your Business LLC"
                            required
                        />
                        @error('name')<div class="hint" style="color:#b91c1c;font-weight:800;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="email">
                            Email <span class="req">Required</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="input"
                            placeholder="you@business.com"
                            required
                        />
                        @error('email')<div class="hint" style="color:#b91c1c;font-weight:800;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="password">
                            Password <span class="req">Required</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="input"
                            placeholder="Create a strong password"
                            required
                        />
                        <div class="hint">
                            Use at least 8 characters. Mix letters, numbers, and symbols for better security.
                        </div>
                        @error('password')<div class="hint" style="color:#b91c1c;font-weight:800;">{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label class="label" for="password_confirmation">
                            Confirm Password <span class="req">Required</span>
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            class="input"
                            placeholder="Re-enter your password"
                            required
                        />
                    </div>

                    <button class="btn" type="submit">Create account</button>

                    <div class="row">
                        <span class="mini">Already have an account?</span>
                        <a class="link" href="{{ route('merchant.login') }}">Login</a>
                    </div>
                </form>
            </div>
        </section>

    </div>
</div>
@endsection
