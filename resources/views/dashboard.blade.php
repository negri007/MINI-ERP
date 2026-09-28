@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        <a href="{{ route('vendas.create') }}" class="btn btn-primary">Nova Venda</a>
    </div>

    {{-- Vendas do mês --}}
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card text-bg-primary h-100">
                <div class="card-body">
                    <div class="small">Faturamento do mês</div>
                    <div class="display-6">R$ {{ number_format($mes['faturamento'], 2, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-bg-success h-100">
                <div class="card-body">
                    <div class="small">Vendas no mês</div>
                    <div class="display-6">{{ $mes['quantidade'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartões com os totais de cada cadastro --}}
    <div class="row g-3 mb-4">
        @foreach ([
            ['Categorias', $totais['categorias'], 'categorias.index', 'primary'],
            ['Fornecedores', $totais['fornecedores'], 'fornecedores.index', 'success'],
            ['Clientes', $totais['clientes'], 'clientes.index', 'warning'],
            ['Produtos', $totais['produtos'], 'produtos.index', 'info'],
        ] as [$titulo, $total, $rota, $cor])
            <div class="col-sm-6 col-lg-3">
                <div class="card border-{{ $cor }} h-100">
                    <div class="card-body">
                        <h2 class="h6 text-muted">{{ $titulo }}</h2>
                        <p class="display-6 mb-2">{{ $total }}</p>
                        <a href="{{ route($rota) }}" class="btn btn-sm btn-outline-{{ $cor }}">Ver todos</a>
                    </div>
                </div>
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
            <span>Produtos com estoque baixo (no mínimo ou abaixo)</span>
            <a href="{{ route('produtos.index', ['estoque_baixo' => 1]) }}" class="small">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
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
                            <td class="text-end">
                                <span class="badge {{ $produto->estoque == 0 ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $produto->estoque }}</span>
                            </td>
                            <td class="text-end">{{ $produto->estoque_minimo }}</td>
                            <td class="text-end">
                                <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="btn btn-sm btn-outline-primary">Repor</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Nenhum produto com estoque baixo.</td>
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

    new Chart(document.getElementById('graficoVendas'), {
        type: 'bar',
        data: {
            labels: vendas.labels,
            datasets: [{ label: 'Faturamento (R$)', data: vendas.valores, backgroundColor: '#0d6efd' }],
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } },
        },
    });

    new Chart(document.getElementById('graficoCategorias'), {
        type: 'doughnut',
        data: {
            labels: categorias.labels,
            datasets: [{ data: categorias.valores }],
        },
        options: { plugins: { legend: { position: 'bottom' } } },
    });
</script>
@endpush
