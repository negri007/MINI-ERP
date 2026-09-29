<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mini ERP') - Mini ERP</title>

    {{-- Bootstrap 5 (CSS) salvo dentro do projeto: funciona sem internet --}}
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Tema "Mata": base escura com verde-mata, amarelo-sol e azul-céu --}}
    <link href="{{ asset('css/tema.css') }}" rel="stylesheet">
</head>
<body>
    @php
        // Menu lateral: [grupo, [título, rota, padrão da rota ativa, ícone]]
        $menu = [
            [null, [
                ['Dashboard', 'dashboard', 'dashboard', 'inicio'],
            ]],
            ['Cadastros', [
                ['Categorias', 'categorias.index', 'categorias.*', 'etiqueta'],
                ['Fornecedores', 'fornecedores.index', 'fornecedores.*', 'caminhao'],
                ['Clientes', 'clientes.index', 'clientes.*', 'pessoas'],
                ['Produtos', 'produtos.index', 'produtos.*', 'caixa'],
            ]],
            ['Operações', [
                ['Vendas', 'vendas.index', 'vendas.*', 'carrinho'],
                ['Estoque', 'estoque.index', 'estoque.*', 'camadas'],
            ]],
            ['Relatórios', [
                ['Vendas por período', 'relatorios.vendas', 'relatorios.vendas*', 'grafico'],
            ]],
        ];

        // Comandos do "Balcão rápido" (Ctrl + K): [grupo, título, endereço, ícone]
        $comandos = [
            ['Criar', 'Nova venda', route('vendas.create'), '+'],
            ['Criar', 'Novo produto', route('produtos.create'), '+'],
            ['Criar', 'Novo cliente', route('clientes.create'), '+'],
            ['Criar', 'Nova categoria', route('categorias.create'), '+'],
            ['Criar', 'Novo fornecedor', route('fornecedores.create'), '+'],
            ['Criar', 'Entrada ou saída de estoque', route('estoque.create'), '±'],
            ['Consultar', 'Produtos com estoque baixo', route('produtos.index', ['estoque_baixo' => 1]), '!'],
            ['Consultar', 'Vendas canceladas', route('vendas.index', ['status' => 'cancelada']), '×'],
            ['Consultar', 'Relatório do mês', route('relatorios.vendas'), '%'],
            ['Consultar', 'Exportar vendas do mês (Excel)', route('relatorios.vendas.exportar'), '↓'],
        ];

        // Telas onde o Balcão rápido oferece "buscar o texto digitado"
        $rotasBusca = [
            'Produtos' => route('produtos.index'),
            'Clientes' => route('clientes.index'),
            'Vendas' => route('vendas.index'),
        ];

        foreach ($menu as [, $itens]) {
            foreach ($itens as [$titulo, $rota]) {
                $comandos[] = ['Ir para', $titulo, route($rota), '→'];
            }
        }

        // Iniciais do usuário para o avatar ("Administrador" -> "AD")
        $usuario = auth()->user();
        $partes = preg_split('/\s+/', trim($usuario->name));
        $iniciais = mb_strtoupper(count($partes) > 1 ? mb_substr($partes[0], 0, 1).mb_substr(end($partes), 0, 1) : mb_substr($partes[0], 0, 2));
    @endphp

    <div class="app">

        {{-- Menu lateral --}}
        <aside class="lateral" id="lateral">
            <a href="{{ route('dashboard') }}" class="logo"><span class="marca">ME</span> Mini ERP</a>

            <nav aria-label="Menu principal">
                @foreach ($menu as [$grupo, $itens])
                    @if ($grupo)
                        <div class="menu-grupo">{{ $grupo }}</div>
                    @endif
                    @foreach ($itens as [$titulo, $rota, $padrao, $icone])
                        <a href="{{ route($rota) }}" class="menu-item {{ request()->routeIs($padrao) ? 'ativo' : '' }}">
                            @include('partials.icone', ['nome' => $icone])
                            {{ $titulo }}
                            {{-- Contador de produtos para repor (vem do AppServiceProvider) --}}
                            @if ($rota === 'produtos.index' && $qtdEstoqueBaixo > 0)
                                <span class="contador" title="Produtos com estoque baixo">{{ $qtdEstoqueBaixo }}</span>
                            @endif
                        </a>
                    @endforeach
                @endforeach
            </nav>

            {{-- Usuário logado e botão de sair --}}
            <div class="usuario">
                <span class="avatar">{{ $iniciais }}</span>
                <div class="text-truncate">
                    <div class="nome text-truncate">{{ $usuario->name }}</div>
                    <small class="text-truncate">{{ $usuario->email }}</small>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="sair" title="Sair" aria-label="Sair">@include('partials.icone', ['nome' => 'sair'])</button>
                </form>
            </div>
        </aside>

        <div class="principal">

            {{-- Barra do topo: menu (celular), busca rápida e nova venda --}}
            <header class="topo">
                <button type="button" class="btn btn-secondary abrir-menu" id="abrirMenu" aria-label="Abrir menu" style="width: 42px; padding: .5rem">
                    @include('partials.icone', ['nome' => 'menu'])
                </button>
                <button type="button" class="abrir-rapido" data-abrir-rapido>
                    <span style="display: contents">@include('partials.icone', ['nome' => 'busca'])</span>
                    <span>Buscar ou ir para…</span>
                    <kbd>Ctrl K</kbd>
                </button>
                <a href="{{ route('vendas.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2">
                    <span style="width: 16px; height: 16px; display: inline-flex">@include('partials.icone', ['nome' => 'mais'])</span>
                    Nova venda
                </a>
            </header>

            {{-- Conteúdo da página --}}
            <main class="conteudo">

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

                @yield('content')
            </main>
        </div>
    </div>

    {{-- Modal de confirmação usado pelos botões "Excluir" e "Cancelar venda" --}}
    <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tem certeza?</h5>
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

    {{-- Balcão rápido: busca de telas e ações (Ctrl + K) --}}
    <div class="balcao-rapido" id="balcaoRapido" hidden>
        <div class="br-caixa" role="dialog" aria-label="Busca rápida">
            <input type="text" id="brBusca" placeholder="Buscar ou ir para…" autocomplete="off">
            <ul class="br-lista" id="brLista"></ul>
            <div class="br-rodape">
                <span><kbd>↑</kbd> <kbd>↓</kbd> escolher</span>
                <span><kbd>Enter</kbd> abrir</span>
                <span><kbd>Esc</kbd> fechar</span>
            </div>
        </div>
    </div>
    <script>
        // Lista de comandos montada no PHP acima, usada pelo mini-erp.js
        window.COMANDOS = @json($comandos);
        window.ROTAS_BUSCA = @json($rotasBusca);
    </script>

    {{-- Bootstrap 5 (JS) e scripts do sistema (máscaras, confirmação, busca rápida, menu no celular) --}}
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/mini-erp.js') }}"></script>

    {{-- Scripts específicos de cada página (ex.: gráficos, itens da venda) --}}
    @stack('scripts')
</body>
</html>
