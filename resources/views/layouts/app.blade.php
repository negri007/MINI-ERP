<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mini ERP') - Mini ERP</title>

    {{-- Bootstrap 5 (CSS) salvo dentro do projeto: funciona sem internet --}}
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Estilos da sidebar --}}
    <style>
        .sidebar {
            width: 220px;
            min-height: calc(100vh - 56px);
        }
        .sidebar .nav-link {
            color: #333;
        }
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: #fff;
        }
        .sidebar .titulo-menu {
            font-size: .75rem;
            text-transform: uppercase;
            color: #6c757d;
            margin: 1rem 0 .25rem .75rem;
        }
    </style>
</head>
<body class="bg-light">

    {{-- Navbar superior --}}
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">Mini ERP</a>

            {{-- Usuário logado e botão de sair --}}
            <div class="d-flex align-items-center gap-3">
                <span class="text-white-50 small">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Sair</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="d-flex">

        {{-- Sidebar lateral com os links do sistema --}}
        <aside class="sidebar bg-white border-end p-3 flex-shrink-0">
            <ul class="nav nav-pills flex-column gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                </li>

                {{-- Cadastros --}}
                <li class="titulo-menu">Cadastros</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('fornecedores.*') ? 'active' : '' }}" href="{{ route('fornecedores.index') }}">Fornecedores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">Clientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}" href="{{ route('produtos.index') }}">Produtos</a>
                </li>

                {{-- Operações do dia a dia --}}
                <li class="titulo-menu">Operações</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('vendas.*') ? 'active' : '' }}" href="{{ route('vendas.index') }}">Vendas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('estoque.*') ? 'active' : '' }}" href="{{ route('estoque.index') }}">Estoque</a>
                </li>

                {{-- Relatórios --}}
                <li class="titulo-menu">Relatórios</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('relatorios.vendas*') ? 'active' : '' }}" href="{{ route('relatorios.vendas') }}">Vendas por período</a>
                </li>
            </ul>
        </aside>

        {{-- Área principal de conteúdo --}}
        <main class="flex-grow-1 p-4">

            {{-- Mensagem de sucesso --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            {{-- Mensagem de erro --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            {{-- Conteúdo de cada página --}}
            @yield('content')

        </main>

    </div>

    {{-- Modal de confirmação usado pelos botões "Excluir" e "Cancelar venda" --}}
    <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="modalConfirmarTexto">Deseja realmente continuar?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
                    <button type="button" class="btn btn-danger" id="modalConfirmarBotao">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap 5 (JS) e scripts do sistema (máscaras e confirmação) --}}
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/mini-erp.js') }}"></script>

    {{-- Scripts específicos de cada página (ex.: gráficos, itens da venda) --}}
    @stack('scripts')
</body>
</html>
