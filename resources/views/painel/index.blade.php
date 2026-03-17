@extends('layouts.app')
@section('title', 'Painel — SCGD Viana')

@section('content')
    @include('partials.header')
    <div class="app-layout">
        @include('partials.sidebar')
        <main class="main" id="main-content">
            @include('partials.sections.inicio')
            @include('partials.sections.ocorrencias')
            @include('partials.sections.pessoas')
            @include('partials.sections.detencoes')
            @include('partials.sections.evidencias')
            @include('partials.sections.investigacoes')
            @include('partials.sections.despachos')
            @include('partials.sections.patrulhas')
            @include('partials.sections.alertas')
            @include('partials.sections.viaturas')
            @include('partials.sections.armamento')
            @include('partials.sections.mensagens')
            @include('partials.sections.relatorios')
            @include('partials.sections.identidade')
            @include('partials.sections.configuracoes')
            @include('partials.sections.logs')
        </main>
    </div>

    @include('partials.modals')
    <div id="loading-overlay"><div class="loader"></div></div>
    <div id="toast-container"></div>

    <script>
        const APP = {
            csrf: '{{ csrf_token() }}',
            userId: {{ auth()->id() }},
            perfil: '{{ auth()->user()->perfil->nome }}',
            isAdmin: {{ auth()->user()->isAdmin() ? 'true' : 'false' }},
            isComandante: {{ auth()->user()->isComandante() ? 'true' : 'false' }},
            userName: '{{ auth()->user()->agente?->nome ?? "Admin" }}',
            userEmail: '{{ auth()->user()->email }}',
            unidadeId: {{ auth()->user()->agente?->unidade_id ?? 'null' }},
        };
    </script>
    <script src="{{ asset('js/painel.js') }}"></script>
@endsection