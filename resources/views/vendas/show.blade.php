@extends('layouts.app')

@section('title', "Venda #{$venda->id}")

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">
            Venda #{{ $venda->id }}
            @if ($venda->estaCancelada())
                <span class="badge bg-secondary fs-6 align-middle">Cancelada</span>
            @else
                <span class="badge bg-success fs-6 align-middle">Concluída</span>
            @endif
        </h1>
        <div>
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Voltar</a>

            {{-- Cancelar devolve os produtos ao estoque --}}
            @unless ($venda->estaCancelada())
                <form action="{{ route('vendas.cancelar', $venda) }}" method="POST" class="d-inline"
                      data-confirmar="Cancelar a venda #{{ $venda->id }}? Os produtos voltarão para o estoque.">
                    @csrf
                    <button type="submit" class="btn btn-danger">Cancelar venda</button>
                </form>
            @endunless
        </div>
    </div>

    @error('venda')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    {{-- Dados da venda --}}
    <div class="card mb-3">
        <div class="card-body row">
            <div class="col-md-4"><strong>Cliente:</strong> {{ $venda->cliente->nome }}</div>
            <div class="col-md-3"><strong>Data:</strong> {{ $venda->data->format('d/m/Y') }}</div>
            <div class="col-md-5"><strong>Registrada por:</strong> {{ $venda->usuario->name ?? '-' }} em {{ $venda->created_at->format('d/m/Y H:i') }}</div>
            @if ($venda->observacao)
                <div class="col-12 mt-2"><strong>Observação:</strong> {{ $venda->observacao }}</div>
            @endif
        </div>
    </div>

    {{-- Itens --}}
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
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
                            <td>{{ $item->produto->nome }}</td>
                            <td class="text-center">{{ $item->quantidade }}</td>
                            <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                            <td class="text-end">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th class="text-end fs-5">R$ {{ number_format($venda->total, 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
