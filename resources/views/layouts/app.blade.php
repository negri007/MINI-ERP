<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mini ERP') - Mini ERP</title>

    {{-- Bootstrap 5 (CSS) salvo dentro do projeto: funciona sem internet --}}
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">

    {{-- Tema "Balcão": muda só a aparência, por cima do Bootstrap --}}
    <link href="{{ asset('css/balcao.css') }}" rel="stylesheet">
</head>
<body>
    @php
        // Itens do menu (fichário): [grupo, cor da etiqueta, [título, rota, padrão da rota ativa]]
        $menu = [
            ['Início', 'var(--tinta)', [
                ['Dashboard', 'dashboard', 'dashboard'],
            ]],
            ['Cadastros', 'var(--carbono)', [
                ['Categorias', 'categorias.index', 'categorias.*'],
                ['Fornecedores', 'fornecedores.index', 'fornecedores.*'],
                ['Clientes', 'clientes.index', 'clientes.*'],
                ['Produtos', 'produtos.index', 'produtos.*'],
            ]],
            ['Operações', 'var(--caixa)', [
                ['Vendas', 'vendas.index', 'vendas.*'],
                ['Estoque', 'estoque.index', 'estoque.*'],
            ]],
            ['Relatórios', 'var(--carimbo)', [
                ['Vendas por período', 'relatorios.vendas', 'relatorios.vendas*'],
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

        foreach ($menu as [, , $itens]) {
            foreach ($itens as [$titulo, $rota]) {
                $comandos[] = ['Ir para', $titulo, route($rota), '→'];
            }
        }
    @endphp

    {{-- Topo: marca, Balcão rápido e usuário --}}
    <header class="topo">
        <a class="marca" href="{{ route('dashboard') }}">
            <span class="selo">ME</span>
            <span>Mini <em>ERP</em></span>
        </a>

        {{-- Abre a busca de comandos (também abre com Ctrl + K ou /) --}}
        <button type="button" class="abrir-rapido" data-abrir-rapido aria-haspopup="dialog" aria-keyshortcuts="Control+K /">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <span>Ir para… ou fazer…</span>
            <kbd data-tecla-ctrl>Ctrl</kbd><kbd>K</kbd>
        </button>

        <div class="usuario">
            <span class="hoje">{{ now()->locale('pt_BR')->translatedFormat('D, d \\d\\e M') }}</span>
            <span>{{ auth()->user()->name }}</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary">Sair</button>
            </form>
        </div>
    </header>

    <div class="mesa">

        {{-- Fichário: cada item do menu é uma aba de pasta --}}
        <nav class="fichario" aria-label="Menu principal">
            @foreach ($menu as [$grupo, $cor, $itens])
                <div class="grupo" style="--cor-grupo: {{ $cor }}">{{ $grupo }}</div>
                @foreach ($itens as [$titulo, $rota, $padrao])
                    @php($ativa = request()->routeIs($padrao))
                    <a href="{{ route($rota) }}" style="--cor-grupo: {{ $cor }}"
                       class="aba {{ $ativa ? 'ativa' : '' }}" @if ($ativa) aria-current="page" @endif>
                        {{ $titulo }} <span class="atalho" aria-hidden="true">→</span>
                    </a>
                @endforeach
            @endforeach
        </nav>

        {{-- Folha de trabalho: o conteúdo de cada página --}}
        <main class="folha">

            {{-- Mensagem de sucesso --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            {{-- Mensagem de erro --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
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
        <div class="br-caixa" role="dialog" aria-modal="true" aria-label="Balcão rápido">
            <input type="text" id="brBusca" placeholder="Ir para… ou fazer…" autocomplete="off"
                   role="combobox" aria-expanded="true" aria-controls="brLista" aria-autocomplete="list" aria-label="Ir para uma tela ou fazer uma ação">
            <ul class="br-lista" id="brLista" role="listbox" aria-label="Resultados"></ul>
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

    {{-- Bootstrap 5 (JS) e scripts do sistema (máscaras, confirmação, Balcão rápido) --}}
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/mini-erp.js') }}"></script>

    {{-- Scripts específicos de cada página (ex.: gráficos, itens da venda) --}}
    @stack('scripts')
</body>
</html>
