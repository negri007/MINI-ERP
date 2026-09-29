{{--
    Cartão "Primeiros passos" do Dashboard. Cada passo se marca sozinho pelo banco
    (o DashboardController monta $passos). "Ocultar" guarda a escolha no navegador;
    o comando "Mostrar primeiros passos" da busca rápida (Ctrl + K) traz de volta.
    Uso: @include('partials.primeiros-passos', ['passos' => $passos])
--}}
@php
    $feitos = collect($passos)->where('feito', true)->count();
    $total = count($passos);
    $clientePendente = collect($passos)->firstWhere('chave', 'cliente')['feito'] === false;
@endphp
<section class="card passos" id="primeirosPassos" aria-labelledby="passosTitulo">
    <div class="card-header">
        <h2 class="h6 mb-0" id="passosTitulo">Primeiros passos</h2>
        <button type="button" class="btn btn-sm btn-secondary" data-ocultar-passos>Ocultar</button>
    </div>
    <div class="card-body">
        <p class="passos-contagem" id="passosContagem">{{ $feitos === $total ? 'Tudo pronto: ' : '' }}{{ $feitos }} de {{ $total }} feitos</p>
        <div class="passos-barra" role="progressbar" aria-labelledby="passosContagem"
             aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $feitos }}">
            <span style="width: {{ round($feitos / $total * 100) }}%"></span>
        </div>

        <ol class="passos-lista">
            @foreach ($passos as $passo)
                <li class="{{ $passo['feito'] ? 'feito' : '' }}">
                    <span class="passos-marca" aria-hidden="true">{{ $passo['feito'] ? '✓' : '' }}</span>
                    <span class="passos-texto">{{ $passo['titulo'] }} <span class="visually-hidden">({{ $passo['feito'] ? 'feito' : 'pendente' }})</span></span>
                    @if ($passo['feito'])
                        <span class="passos-feito" aria-hidden="true">feito</span>
                    @else
                        <a href="{{ $passo['url'] }}" class="btn btn-sm btn-outline-primary">{{ $passo['acao'] }}</a>
                    @endif
                </li>
            @endforeach
        </ol>

        @if ($clientePendente)
            <p class="form-text mb-0">Para vender sem identificar o cliente, use Consumidor final: ele já vem pronto.</p>
        @endif
    </div>
</section>
