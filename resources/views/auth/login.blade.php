@extends('layouts.app')
@section('title', 'Login — SCGD Viana')

@push('styles')
<style>
    body.login-body {
        background: #11306ed3;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        overflow: auto;
    }

    .login-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 48px 40px;
        width: 580px;
        max-width: 720px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    form#login-form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    form#login-form .form-group {
        width: 90%;
    }
    

    .login-logo {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        margin: 0 auto 24px;
        display: block;
    }

    .login-title {
        font-size: 22px;
        font-weight: 700;
        text-align: center;
        color: #111827;
        margin-bottom: 4px;
    }

    .login-sub {
        font-size: 14px;
        color: #6b7280;
        text-align: center;
        margin-bottom: 32px;
    }

    .login-label {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }

    .login-input {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        margin-bottom: 16px;
        outline: none;
        transition: border 0.2s;
    }

    .login-input:focus {
        border-color: #426dfa;
    }

    .login-btn {
        width: 60%;
        padding: 11px;
        background: #111827;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.2s;
    }

    .login-btn:hover {
        background: #1f2937;
    }

    .login-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .login-error {
        color: #dc2626;
        font-size: 13px;
        text-align: center;
        margin-bottom: 12px;
    }

    .login-footer {
        text-align: center;
        margin-top: 24px;
        font-size: 12px;
        color: #9ca3af;
    }

    .login-link {
        text-align: right;
        margin-bottom: 20px;
    }

    .login-link a {
        font-size: 13px;
        color: #771eeb;
        text-decoration: none;
    }

    .spinner-btn {
        border: 2px solid #fff;
        border-top: 2px solid transparent;
        border-radius: 50%;
        width: 14px;
        height: 14px;
        animation: spin .7s linear infinite;
        display: none;
    }

    .spinner-btn.active {
        display: block;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@section('content')

<body class="login-body">
    <div class="login-card">
        <img src="{{ asset('img/bnd.png') }}" alt="Logo" class="login-logo">
        <h1 class="login-title">Bem-vindo</h1>
        <p class="login-sub">Para entrar, por favor, insira suas credenciais</p>

        <form method="POST" action="{{ route('login.submit') }}" id="login-form">
            @csrf
          <div class="form-group">
            <label class="login-label">Email Institucional</label>
            <input class="login-input" name="email" type="email" placeholder="nome@policia-viana.ao" required>
</div>
           <div class="form-group">
            <label class="login-label">Palavra-Passe</label>
            <input class="login-input" name="password" type="password" placeholder="••••••••" required>
</div>
            @if ($errors->any())
            <div class="login-error">{{ $errors->first() }}</div>
            @endif
            <div class="form-group" style="width:100%;display:flex;justify-content:flex-end;">
            <div class="login-link"><a href="#">Esqueceu a palavra-passe?</a></div>
</div>
            <button class="login-btn" type="submit" id="login-btn">
                <span class="spinner-btn" id="login-spinner"></span>
                <span>Entrar</span>
            </button>
        </form>

        <div class="login-footer">
            Polícia Nacional de Angola
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            document.getElementById('login-btn').disabled = true;
            document.getElementById('login-spinner').classList.add('active');
        });
    </script>
</body>
@endsection