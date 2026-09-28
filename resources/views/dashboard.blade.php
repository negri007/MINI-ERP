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

    {{-- Gráficos (Chart.js) --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">Faturamento dos últimos 14 dias</div>
                <div class="card-body"><canvas id="graficoVendas" height="120"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">Produtos por categoria</div>
                <div class="card-body"><canvas id="graficoCategorias"></canvas></div>
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

    // Visual dos gráficos combinando com o tema: tinta preta, carimbo vermelho, fonte de máquina
    Chart.defaults.font.family = "'Plex Mono', monospace";
    Chart.defaults.color = '#6d6558';
    Chart.defaults.borderColor = '#e2d7c3';

    new Chart(document.getElementById('graficoVendas'), {
        type: 'bar',
        data: {
            labels: vendas.labels,
            datasets: [{
                label: 'Faturamento (R$)',
                data: vendas.valores,
                // Barras em tinta; a de hoje em vermelho
                backgroundColor: vendas.valores.map((_, i) => i === vendas.valores.length - 1 ? '#c2362b' : '#1e1b16'),
                borderRadius: 2,
                barPercentage: .6,
            }],
        },
        options: {
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (c) => c.parsed.y.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) } },
            },
            scales: { y: { beginAtZero: true, grid: { borderDash: [3, 3] } }, x: { grid: { display: false } } },
        },
    });

    new Chart(document.getElementById('graficoCategorias'), {
        type: 'doughnut',
        data: {
            labels: categorias.labels,
            datasets: [{
                data: categorias.valores,
                backgroundColor: ['#1e1b16', '#2c4a94', '#2e6b4c', '#d49a2a', '#c2362b', '#8a7f6d'],
                borderColor: '#fffcf5',
                borderWidth: 3,
            }],
        },
        options: { cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } },
    });
</script>
@endpush
