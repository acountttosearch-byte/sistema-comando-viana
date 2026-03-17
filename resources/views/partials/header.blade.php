<header class="topbar">
    <div class="topbar-left">
        <div class="topbar-logo" onclick="showSection('inicio')">
            <img src="{{ asset('img/bnd.png') }}" alt="Logo">
            <div class="topbar-brand">
                <span class="brand-name">SCGD</span>
                <span class="brand-sub">Comando de Viana</span>
            </div>
        </div>
    </div>

    <div class="topbar-center">
        <div class="search-box" id="searchBox">
            <i class='bx bx-search'></i>
            <input type="text" id="searchInput" placeholder="Pesquisar..." autocomplete="off">
            <kbd>⌘K</kbd>
        </div>
    </div>

    <div class="topbar-right">
        <button class="topbar-icon" title="Notificações" onclick="showSection('alertas')">
            <i class='bx bx-bell'></i>
            <span class="notif-dot" id="notif-dot" style="display:none;"></span>
        </button>

        <div class="topbar-user" id="user-dropdown">
            <button class="user-trigger" id="user-trigger">
                <div class="user-avatar">{{ substr(auth()->user()->agente?->nome ?? 'A', 0, 1) }}</div>
                <span class="user-name-short">{{ explode(' ', auth()->user()->agente?->nome ?? 'Admin')[0] }}</span>
                <i class='bx bx-chevron-down'></i>
            </button>
            <div class="user-menu" id="user-menu">
                <div class="user-menu-header">
                    <strong>{{ auth()->user()->agente?->nome ?? 'Admin' }}</strong>
                    <span>{{ auth()->user()->email }}</span>
                    <span class="user-role-tag">{{ auth()->user()->perfil->descricao }}</span>
                </div>
                <div class="user-menu-divider"></div>
                <a class="user-menu-item" href="#" onclick="showSection('configuracoes')"><i class='bx bx-cog'></i> Configurações</a>
                <div class="user-menu-divider"></div>
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="margin:0;">
                    @csrf
                    <button type="button" class="user-menu-item logout" onclick="confirmarLogout(event)"><i class='bx bx-log-out'></i> Sair</button>
                </form>
            </div>
        </div>
    </div>
</header>