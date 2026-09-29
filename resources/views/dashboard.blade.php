@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $hora = now()->hour;
        $saudacao = $hora < 12 ? 'Bom dia' : ($hora < 18 ? 'Boa tarde' : 'Boa noite');
        $primeiroNome = explode(' ', auth()->user()->name)[0];
    @endphp

    {{-- Saudação --}}
    <div class="mb-4">
        <h1 class="h3 mb-0">{{ $saudacao }}, {{ $primeiroNome }}</h1>
        <p class="subtitulo mb-0">{{ ucfirst(now()->locale('pt_BR')->translatedFormat('l, d \\d\\e F')) }} · aqui está o resumo da loja</p>
    </div>

    {{-- Indicadores do mês --}}
    <section class="kpis">
        <div class="card kpi kpi-principal">
            <div class="rotulo">Faturamento de {{ now()->locale('pt_BR')->translatedFormat('F') }}</div>
            <div class="valor"><small>R$</small>{{ number_format($mes['faturamento'], 2, ',', '.') }}</div>
            <div>
                @if (! is_null($mes['variacao']))
                    <span class="variacao {{ $mes['variacao'] >= 0 ? 'sobe' : 'desce' }}">
                        {{ $mes['variacao'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($mes['variacao']), 1, ',', '.') }}%
                    </span>
                    <span class="rodape">vs. mesmo período do mês passado</span>
                @else
                    <span class="rodape ms-0">sem vendas no mês passado para comparar</span>
                @endif
            </div>
        </div>

        <div class="card kpi">
            <div class="rotulo">Vendas no mês <span class="icone verde">@include('partials.icone', ['nome' => 'carrinho'])</span></div>
            <div class="valor">{{ $mes['quantidade'] }}</div>
            <div class="rodape">{{ $mes['hoje'] }} hoje</div>
        </div>

        <div class="card kpi">
            <div class="rotulo">Ticket médio <span class="icone sol">@include('partials.icone', ['nome' => 'dinheiro'])</span></div>
            <div class="valor"><small>R$</small>{{ number_format($mes['ticket_medio'], 2, ',', '.') }}</div>
            <div class="rodape">por venda</div>
        </div>

        <a href="{{ route('produtos.index', ['estoque_baixo' => 1]) }}" class="card kpi text-decoration-none text-reset">
            <div class="rotulo">Para repor <span class="icone perigo">@include('partials.icone', ['nome' => 'alerta'])</span></div>
            <div class="valor">{{ $qtdParaRepor }}</div>
            <div class="rodape">produtos no estoque mínimo</div>
        </a>
    </section>

    <section class="painel">
        {{-- Gráfico de faturamento (Chart.js), com versão em tabela --}}
        <div class="card">
            <div class="card-header">Faturamento dos últimos 14 dias <small class="text-muted">hoje em destaque</small></div>
            <div class="card-body">
                <div class="grafico"><canvas id="graficoVendas" role="img" aria-label="Gráfico de colunas do faturamento por dia"></canvas></div>
                <details class="ver-tabela">
                    <summary>Ver como tabela</summary>
                    <table class="table table-sm">
                        <thead><tr><th>Dia</th><th class="text-end">Faturamento</th></tr></thead>
                        <tbody>
                            @foreach ($grafico['labels'] as $i => $dia)
                                <tr><td>{{ $dia }}</td><td class="text-end">R$ {{ number_format($grafico['valores'][$i], 2, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
            </div>
        </div>

        {{-- Produtos com estoque baixo --}}
        <div class="card">
            <div class="card-header">Estoque baixo <a href="{{ route('produtos.index', ['estoque_baixo' => 1]) }}">ver todos →</a></div>
            <div class="card-body p-0">
                @forelse ($estoqueBaixo as $produto)
                    <div class="estoque-item">
                        <div class="nome text-truncate">{{ $produto->nome }} <small>{{ $produto->categoria->nome ?? '-' }}</small></div>
                        @include('partials.medidor', ['produto' => $produto])
                        <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="btn btn-sm btn-outline-primary">Repor</a>
                    </div>
                @empty
                    <p class="text-muted text-center py-4 mb-0">Nenhum produto com estoque baixo.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Últimas vendas --}}
    <div class="card">
        <div class="card-header">Últimas vendas <a href="{{ route('vendas.index') }}">ver todas →</a></div>
        <div class="card-body p-0">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Venda</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th class="text-center">Itens</th>
                        <th>Situação</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimasVendas as $venda)
                        @php($nomes = preg_split('/\s+/', $venda->cliente->nome))
                        <tr>
                            <td><a href="{{ route('vendas.show', $venda) }}">#{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</a></td>
                            <td>
                                <div class="cliente-cel">
                                    <span class="iniciais">{{ mb_strtoupper(mb_substr($nomes[0], 0, 1).mb_substr(end($nomes), 0, 1)) }}</span>
                                    {{ $venda->cliente->nome }}
                                </div>
                            </td>
                            <td>{{ $venda->data->format('d/m') }}</td>
                            <td class="text-center">{{ $venda->itens_count }}</td>
                            <td>
                                @if ($venda->estaCancelada())
                                    <span class="badge bg-danger">Cancelada</span>
                                @else
                                    <span class="badge bg-success">Concluída</span>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma venda ainda.</td></tr>
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
    const grafico = @json($grafico);

    // Cores validadas com a skill dataviz sobre o cartão escuro (#14211c):
    // destaque verde e contexto cinza, ambos com contraste >= 3:1 e bem separados para daltonismo
    const COR = { destaque: '#2dab6a', contexto: '#6b7076', texto: '#e8efe9', eixo: '#9db0a6', grade: 'rgba(255,255,255,.06)' };
    const reais = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = COR.eixo;
    Chart.defaults.maintainAspectRatio = false;

    // Plugin: escreve o valor em cima da barra de hoje (rótulo direto só no que importa)
    const rotuloHoje = {
        id: 'rotuloHoje',
        afterDatasetsDraw(chart) {
            const barras = chart.getDatasetMeta(0).data;
            const i = barras.length - 1;
            const { ctx } = chart;
            ctx.save();
            ctx.font = "600 12px 'Inter', sans-serif";
            ctx.fillStyle = COR.texto;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'bottom';
            ctx.fillText(reais(chart.data.datasets[0].data[i]), barras[i].x, barras[i].y - 6);
            ctx.restore();
        },
    };

    const hoje = grafico.valores.length - 1;
    new Chart(document.getElementById('graficoVendas'), {
        type: 'bar',
        plugins: [rotuloHoje],
        data: {
            labels: grafico.labels.map((d, i) => (i === hoje ? 'hoje' : d)),
            datasets: [{
                label: 'Faturamento',
                data: grafico.valores,
                backgroundColor: grafico.valores.map((_, i) => (i === hoje ? COR.destaque : COR.contexto)),
                borderRadius: { topLeft: 5, topRight: 5 }, // ponta arredondada, base reta
                borderSkipped: 'bottom',
                maxBarThickness: 26,
            }],
        },
        options: {
            layout: { padding: { top: 24, right: 34 } }, // espaço para o rótulo de hoje não ser cortado
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false }, // uma série só: o título do cartão já diz o que é
                tooltip: { callbacks: { label: (c) => reais(c.parsed.y) } },
            },
            scales: {
                y: { beginAtZero: true, border: { display: false }, grid: { color: COR.grade }, ticks: { maxTicksLimit: 5, callback: (v) => 'R$ ' + v.toLocaleString('pt-BR') } },
                x: { grid: { display: false }, border: { color: 'rgba(255,255,255,.12)' } },
            },
        },
    });
</script>
@endpush
