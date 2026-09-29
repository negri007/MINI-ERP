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

    {{-- Aplica o "menu recolhido" antes de desenhar a página (evita o menu piscar) --}}
    <script>
        try { if (JSON.parse(localStorage.getItem('menu-compacto'))) document.documentElement.classList.add('menu-compacto'); } catch (e) {}
        // "Primeiros passos" ocultado: esconde antes de desenhar (a não ser que tenha pedido para mostrar)
        try {
            if (JSON.parse(localStorage.getItem('primeiros-passos-oculto')) && !location.search.includes('passos=1')) {
                const estilo = document.createElement('style');
                estilo.id = 'passos-oculto-css';
                estilo.textContent = '#primeirosPassos { display: none; }';
                document.head.appendChild(estilo);
            }
        } catch (e) {}
        // Esconde já as dicas que a pessoa marcou como "Entendi" (evita a dica aparecer e sumir)
        try {
            const ocultas = (JSON.parse(localStorage.getItem('dicas-ocultas')) || []).filter((k) => /^[a-z0-9-]+$/.test(k));
            if (ocultas.length) {
                const estilo = document.createElement('style');
                estilo.id = 'dicas-ocultas-css';
                estilo.textContent = ocultas.map((k) => `.dica[data-dica="${k}"] .dica-texto, .dica[data-dica="${k}"] .dica-fechar { display: none; } .dica[data-dica="${k}"] .dica-abrir { display: inline-flex; }`).join(' ');
                document.head.appendChild(estilo);
            }
        } catch (e) {}
    </script>
</head>
<body>
    @php
        // Menu lateral: [seção-mãe, ícone da seção, [itens-filhos: título, rota, padrão da rota ativa, ícone]]
        // A seção null é o Dashboard, que fica solto no topo, sem seção.
        $menu = [
            [null, null, [
                ['Dashboard', 'dashboard', 'dashboard', 'inicio'],
            ]],
            ['Cadastros', 'pasta', [
                ['Categorias', 'categorias.index', 'categorias.*', 'etiqueta'],
                ['Fornecedores', 'fornecedores.index', 'fornecedores.*', 'caminhao'],
                ['Clientes', 'clientes.index', 'clientes.*', 'pessoas'],
                ['Produtos', 'produtos.index', 'produtos.*', 'caixa'],
            ]],
            ['Operações', 'atividade', [
                ['Vendas', 'vendas.index', 'vendas.*', 'carrinho'],
                ['Estoque', 'estoque.index', 'estoque.*', 'camadas'],
            ]],
            ['Relatórios', 'grafico', [
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
            ['Ajuda', 'Mostrar primeiros passos', route('dashboard', ['passos' => 1]), '✓'],
        ];

        // Telas onde o Balcão rápido oferece "buscar o texto digitado"
        $rotasBusca = [
            'Produtos' => route('produtos.index'),
            'Clientes' => route('clientes.index'),
            'Vendas' => route('vendas.index'),
        ];

        foreach ($menu as [, , $itens]) {
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
            <div class="lateral-topo">
                <a href="{{ route('dashboard') }}" class="logo"><span class="marca">ME</span> <span class="rotulo-menu">Mini ERP</span></a>
                {{-- Recolhe o menu para mostrar só os ícones (a escolha fica salva no navegador) --}}
                <button type="button" class="recolher" id="recolherMenu" title="Recolher menu" aria-label="Recolher menu">
                    @include('partials.icone', ['nome' => 'recolher'])
                </button>
            </div>

            <nav class="menu" aria-label="Menu principal">
                @foreach ($menu as [$secao, $iconeSecao, $itens])
                    @if (! $secao)
                        {{-- Itens soltos (sem seção): o Dashboard --}}
                        @foreach ($itens as [$titulo, $rota, $padrao, $icone])
                            <a href="{{ route($rota) }}" title="{{ $titulo }}" class="menu-item raiz {{ request()->routeIs($padrao) ? 'ativo' : '' }}">
                                <span class="secao-icone">@include('partials.icone', ['nome' => $icone])</span>
                                <span class="rotulo-menu">{{ $titulo }}</span>
                            </a>
                        @endforeach
                    @else
                        @php($secaoAtiva = collect($itens)->contains(fn ($i) => request()->routeIs($i[2])))

                        {{-- Seção-mãe: título grande e clicável que abre/fecha os itens-filhos --}}
                        <div class="secao {{ $secaoAtiva ? 'tem-ativo' : '' }}" data-secao="{{ \Illuminate\Support\Str::slug($secao) }}">
                            <button type="button" class="secao-titulo" aria-expanded="true" title="{{ $secao }}">
                                <span class="secao-icone">@include('partials.icone', ['nome' => $iconeSecao])</span>
                                <span class="rotulo-menu">{{ $secao }}</span>

                                {{-- Informação viva ao lado do título --}}
                                @if ($secao === 'Operações' && $vendasHoje > 0)
                                    <span class="secao-info" title="Vendas registradas hoje">{{ $vendasHoje }} hoje</span>
                                @endif

                                <span class="secao-seta">@include('partials.icone', ['nome' => 'seta-baixo'])</span>
                            </button>

                            {{-- Itens-filhos, ligados ao título por "galhos" --}}
                            <div class="secao-itens">
                                <div>
                                    @foreach ($itens as [$titulo, $rota, $padrao, $icone])
                                        <a href="{{ route($rota) }}" title="{{ $titulo }}" class="menu-item filho {{ request()->routeIs($padrao) ? 'ativo' : '' }}">
                                            @include('partials.icone', ['nome' => $icone])
                                            <span class="rotulo-menu">{{ $titulo }}</span>
                                            {{-- Contador de produtos para repor (vem do AppServiceProvider) --}}
                                            @if ($rota === 'produtos.index' && $qtdEstoqueBaixo > 0)
                                                <span class="contador" title="Produtos com estoque baixo">{{ $qtdEstoqueBaixo }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            {{-- Folhas decorativas ao fundo do menu (tema "Mata") --}}
            <svg class="folhas" viewBox="0 0 240 220" aria-hidden="true">
                <path d="M30 220 C40 150 90 110 150 95 C120 140 90 180 30 220Z"/>
                <path d="M60 220 C85 170 140 150 210 150 C170 185 120 205 60 220Z"/>
                <path d="M10 200 C5 150 25 110 70 80 C60 130 45 170 10 200Z"/>
                <path d="M30 220 C60 160 100 125 150 95" fill="none"/>
            </svg>

            {{-- Usuário logado e botão de sair --}}
            <div class="usuario">
                <span class="avatar" title="{{ $usuario->name }}">{{ $iniciais }}</span>
                <div class="text-truncate rotulo-menu">
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
                <button type="button" class="abrir-rapido" data-abrir-rapido aria-label="Buscar ou ir para (Ctrl + K)">
                    <span style="display: contents">@include('partials.icone', ['nome' => 'busca'])</span>
                    <span>Buscar ou ir para…</span>
                    <kbd>Ctrl K</kbd>
                </button>
                {{-- Um amarelo cheio por tela: no Dashboard "Nova venda" é a ação principal;
                     nas outras telas vira contorno, porque a página já tem o seu botão principal.
                     Na própria tela de nova venda o atalho não aparece. --}}
                @unless (request()->routeIs('vendas.create'))
                    <a href="{{ route('vendas.create') }}" class="btn {{ request()->routeIs('dashboard') ? 'btn-primary' : 'btn-outline-primary' }} d-inline-flex align-items-center gap-2">
                        <span style="width: 16px; height: 16px; display: inline-flex">@include('partials.icone', ['nome' => 'mais'])</span>
                        Nova venda
                    </a>
                @endunless
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
    {{-- Os textos e os botões mudam conforme o formulário (data-confirmar-*, data-bloqueio*) --}}
    <div class="modal fade" id="modalConfirmar" tabindex="-1" aria-labelledby="modalConfirmarTitulo" aria-describedby="modalConfirmarTexto" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="modalConfirmarTitulo">Tem certeza?</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body"><p class="mb-0" id="modalConfirmarTexto">Deseja realmente continuar?</p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="modalConfirmarNao">Voltar</button>
                    <a class="btn btn-primary" id="modalConfirmarLink" href="#" hidden></a>
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
