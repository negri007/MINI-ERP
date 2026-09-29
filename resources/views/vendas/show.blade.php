@extends('layouts.app')

@section('title', "Venda #{$venda->id}")

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 no-print">
        <div>
            <h1 class="h3 mb-0">Venda #{{ str_pad($venda->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <p class="subtitulo mb-0">Registrada por {{ $venda->usuario->name ?? '-' }} em {{ $venda->created_at->format('d/m/Y \à\s H:i') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-2" onclick="window.print()">
                @include('partials.icone', ['nome' => 'impressora']) Imprimir
            </button>

            {{-- Cancelar devolve os produtos ao estoque --}}
            @unless ($venda->estaCancelada())
                <form action="{{ route('vendas.cancelar', $venda) }}" method="POST"
                      data-confirmar="Cancelar a venda #{{ $venda->id }}? Os produtos voltarão para o estoque.">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cancelar venda</button>
                </form>
            @endunless
        </div>
    </div>

    @error('venda')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    {{-- A venda, em formato de nota --}}
    <div class="card nota {{ $venda->estaCancelada() ? 'cancelada' : '' }}">
        @if ($venda->estaCancelada())
            <div class="faixa-cancelada">Venda cancelada: os produtos já voltaram para o estoque.</div>
        @endif

        <div class="cabecalho-nota">
            <div>
                <div class="text-muted small">Mini ERP · comprovante de venda</div>
                <div class="numero-venda">Nº {{ str_pad($venda->id, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            @if ($venda->estaCancelada())
                <span class="badge bg-danger">Cancelada</span>
            @else
                <span class="badge bg-success">Concluída</span>
            @endif
        </div>

        <div class="dados">
            <div><small>Cliente</small>{{ $venda->cliente->nome }}</div>
            <div><small>CPF/CNPJ</small>{{ $venda->cliente->cpf_cnpj ?? '-' }}</div>
            <div><small>Data</small>{{ $venda->data->format('d/m/Y') }}</div>
        </div>

        {{-- Itens --}}
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Quantidade</th>
                    <th class="text-end">Preço unitário</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($venda->itens as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->produto->nome }}</td>
                        <td class="text-center">{{ $item->quantidade }}</td>
                        <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                        <td class="text-end">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($venda->observacao)
            <div class="px-4 pt-3 text-muted"><strong>Observação:</strong> {{ $venda->observacao }}</div>
        @endif

        <div class="total-nota">
            <span class="text-muted">{{ $venda->itens->sum('quantidade') }} item(ns)</span>
            <span class="valor">R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
        </div>
    </div>
@endsection
