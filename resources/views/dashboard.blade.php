@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <div class="rotulo">{{ now()->locale('pt_BR')->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</div>
            <h1 class="h3 mb-0">Fechamento parcial do caixa</h1>
        </div>
        <a href="{{ route('vendas.create') }}" class="btn btn-primary">Nova Venda</a>
    </div>

    {{-- Caixa do mês (painel escuro) + fita com as últimas vendas --}}
    <section class="caixa-do-dia">
        <div class="painel-caixa">
            <div class="rotulo">Faturamento de {{ now()->locale('pt_BR')->translatedFormat('F') }}</div>
            <div class="valor"><small>R$</small>{{ number_format($mes['faturamento'], 2, ',', '.') }}</div>
            <div class="resumo">
                <div><strong>{{ $mes['quantidade'] }}</strong> vendas no mês</div>
                <div><strong>R$ {{ number_format($mes['quantidade'] ? $mes['faturamento'] / $mes['quantidade'] : 0, 2, ',', '.') }}</strong> ticket médio</div>
                <div><strong>{{ $estoqueBaixo->count() }}</strong> produtos para repor</div>
            </div>
        </div>

        <div class="fita">
            <div class="rotulo mb-2">Fita de caixa · últimas vendas</div>
            @forelse ($ultimasVendas as $venda)
                <div class="linha">
                    <a href="{{ route('vendas.show', $venda) }}">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }} {{ \Illuminate\Support\Str::limit($venda->cliente->nome, 16) }}</a>
                    <span class="pontilhado"></span>
                    @if ($venda->estaCancelada())
                        <s class="text-danger">{{ number_format($venda->total, 2, ',', '.') }}</s>
                    @else
                        <span>{{ number_format($venda->total, 2, ',', '.') }}</span>
                    @endif
                </div>
            @empty
                <div class="text-muted">Nenhuma venda ainda.</div>
            @endforelse
        </div>
    </section>

    {{-- Gavetas: totais de cada cadastro --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['Categorias', $totais['categorias'], 'categorias.index'],
            ['Fornecedores', $totais['fornecedores'], 'fornecedores.index'],
            ['Clientes', $totais['clientes'], 'clientes.index'],
            ['Produtos', $totais['produtos'], 'produtos.index'],
        ] as [$titulo, $total, $rota])
            <div class="col-sm-6 col-lg-3">
                <a href="{{ route($rota) }}" class="gaveta" style="--cor-grupo: var(--carbono)">
                    <div class="rotulo">{{ $titulo }}</div>
                    <div class="qtd">{{ $total }}</div>
                    <div class="abrir">abrir gaveta →</div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Gráficos (Chart.js). Cada gráfico tem também uma versão em tabela (acessibilidade) --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Faturamento dos últimos 14 dias · hoje em destaque</div>
                <div class="card-body">
                    <div class="grafico" style="height: 260px"><canvas id="graficoVendas" aria-label="Gráfico de colunas do faturamento por dia" role="img"></canvas></div>
                    <details class="ver-tabela">
                        <summary>Ver como tabela</summary>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Dia</th><th class="text-end">Faturamento</th></tr></thead>
                            <tbody>
                                @foreach ($graficoVendas['labels'] as $i => $dia)
                                    <tr><td>{{ $dia }}</td><td class="text-end">R$ {{ number_format($graficoVendas['valores'][$i], 2, ',', '.') }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Produtos por categoria</div>
                <div class="card-body">
                    <div class="grafico" style="height: {{ max(120, count($graficoCategorias['labels']) * 44) }}px"><canvas id="graficoCategorias" aria-label="Gráfico de barras com a quantidade de produtos por categoria" role="img"></canvas></div>
                    <details class="ver-tabela">
                        <summary>Ver como tabela</summary>
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Categoria</th><th class="text-end">Produtos</th></tr></thead>
                            <tbody>
                                @foreach ($graficoCategorias['labels'] as $i => $categoria)
                                    <tr><td>{{ $categoria }}</td><td class="text-end">{{ $graficoCategorias['valores'][$i] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </div>
            </div>
        </div>
    </div>

    {{-- Produtos com estoque baixo --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Para repor: no estoque mínimo ou abaixo</span>
            <a href="{{ route('produtos.index', ['estoque_baixo' => 1]) }}">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Nível</th>
                        <th class="text-end">Estoque</th>
                        <th class="text-end">Mínimo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($estoqueBaixo as $produto)
                        <tr>
                            <td>{{ $produto->nome }}</td>
                            <td>{{ $produto->categoria->nome ?? '-' }}</td>
                            <td>@include('partials.regua', ['produto' => $produto])</td>
                            <td class="text-end">{{ $produto->estoque }}</td>
                            <td class="text-end">{{ $produto->estoque_minimo }}</td>
                            <td class="text-end">
                                <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="btn btn-sm btn-outline-primary">Repor</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum produto com estoque baixo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Chart.js salvo dentro do projeto (funciona sem internet) --}}
<script src="{{ asset('vendor/chartjs/chart.umd.js') }}"></script>
<script>
    // Dados enviados pelo DashboardController
    const vendas = @json($graficoVendas);
    const categorias = @json($graficoCategorias);

    // Cores dos gráficos, validadas com a skill dataviz (contraste >= 3:1 sobre a folha):
    // destaque = azul carbono; contexto = cinza quente; texto sempre em tinta, nunca na cor da barra
    const COR = { destaque: '#3456a3', contexto: '#8a7f6d', texto: '#1e1b16', eixo: '#6d6558', grade: '#ebe3d3', folha: '#fffcf5' };
    const reais = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const reaisCurto = (v) => 'R$ ' + v.toLocaleString('pt-BR', { maximumFractionDigits: 0 });

    Chart.defaults.font.family = "'Plex Mono', monospace";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = COR.eixo;
    Chart.defaults.maintainAspectRatio = false;

    // Plugin: escreve o valor na ponta de barras escolhidas (rótulo direto, sem poluir o gráfico)
    const rotuloNaPonta = {
        id: 'rotuloNaPonta',
        afterDatasetsDraw(chart, _args, opcoes) {
            const { ctx } = chart;
            const barras = chart.getDatasetMeta(0).data;
            ctx.save();
            ctx.font = "500 11px 'Plex Mono', monospace";
            ctx.fillStyle = COR.texto;
            barras.forEach((barra, i) => {
                if (!opcoes.mostrar(i)) return;
                const texto = opcoes.formatar(chart.data.datasets[0].data[i], i);
                if (chart.options.indexAxis === 'y') {
                    ctx.textAlign = 'left'; ctx.textBaseline = 'middle';
                    ctx.fillText(texto, barra.x + 6, barra.y);
                } else {
                    ctx.textAlign = 'center'; ctx.textBaseline = 'bottom';
                    ctx.fillText(texto, barra.x, barra.y - 6);
                }
            });
            ctx.restore();
        },
    };

    // Gráfico 1: colunas por dia. Forma "destaque": hoje em azul, os outros dias em cinza de contexto
    const hoje = vendas.valores.length - 1;
    new Chart(document.getElementById('graficoVendas'), {
        type: 'bar',
        plugins: [rotuloNaPonta],
        data: {
            labels: vendas.labels.map((d, i) => (i === hoje ? 'hoje' : d)),
            datasets: [{
                label: 'Faturamento',
                data: vendas.valores,
                backgroundColor: vendas.valores.map((_, i) => (i === hoje ? COR.destaque : COR.contexto)),
                borderRadius: { topLeft: 4, topRight: 4 }, // ponta arredondada, base reta
                borderSkipped: 'bottom',
                maxBarThickness: 24,
            }],
        },
        options: {
            layout: { padding: { top: 22, right: 30 } }, // espaço para o rótulo da última barra não ser cortado
            interaction: { mode: 'index', intersect: false }, // passar o mouse em qualquer ponto da coluna
            plugins: {
                legend: { display: false }, // uma série só: o título já diz o que é
                tooltip: { callbacks: { label: (c) => reais(c.parsed.y) } },
                rotuloNaPonta: { mostrar: (i) => i === hoje, formatar: (v) => reais(v) },
            },
            scales: {
                y: { beginAtZero: true, border: { display: false }, grid: { color: COR.grade }, ticks: { maxTicksLimit: 5, callback: reaisCurto } },
                x: { grid: { display: false }, border: { color: '#c9bea9' } },
            },
        },
    });

    // Gráfico 2: barras horizontais (valores próximos se comparam melhor em barra do que em rosca)
    const ordem = categorias.labels.map((l, i) => [l, categorias.valores[i]]).sort((a, b) => b[1] - a[1]);
    new Chart(document.getElementById('graficoCategorias'), {
        type: 'bar',
        plugins: [rotuloNaPonta],
        data: {
            labels: ordem.map((o) => o[0]),
            datasets: [{
                label: 'Produtos',
                data: ordem.map((o) => o[1]),
                backgroundColor: COR.destaque, // uma série = uma cor para todas as barras
                borderRadius: { topRight: 4, bottomRight: 4 },
                borderSkipped: 'left',
                maxBarThickness: 20,
            }],
        },
        options: {
            indexAxis: 'y',
            layout: { padding: { right: 28 } },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => `${c.parsed.x} produto(s)` } },
                rotuloNaPonta: { mostrar: () => true, formatar: (v) => v },
            },
            scales: {
                x: { display: false, beginAtZero: true },
                y: { grid: { display: false }, border: { color: '#c9bea9' }, ticks: { color: COR.texto, font: { family: "'Plex Sans', sans-serif", size: 12 } } },
            },
        },
    });
</script>
@endpush
