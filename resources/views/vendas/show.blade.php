@extends('layouts.app')

@section('title', "Venda #{$venda->id}")

@section('content')
    @php
        // Código de barras decorativo: cada dígito do número da venda vira barras de larguras diferentes
        $digitos = str_split(str_pad((string) $venda->id, 8, '0', STR_PAD_LEFT));
        $barras = [[2, 1], [1, 1], [2, 1]]; // barras-guia do início
        foreach ($digitos as $i => $d) {
            $barras[] = [($d % 3) + 1, (($d + $i) % 3) + 1];
            $barras[] = [((int) $d > 5 ? 3 : 1), 1];
        }
        array_push($barras, [1, 1], [2, 1], [1, 0]); // barras-guia do fim
    @endphp

    <div class="d-flex justify-content-between align-items-end mb-4 no-print">
        <div>
            <div class="rotulo">Operações · Vendas</div>
            <h1 class="h3 mb-0">
                Venda #{{ $venda->id }}
                @if ($venda->estaCancelada())
                    <span class="badge bg-secondary selo-status align-middle">Cancelada</span>
                @else
                    <span class="badge bg-success selo-status align-middle">Concluída</span>
                @endif
            </h1>
        </div>
        <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Voltar</a>
    </div>

    @error('venda')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

    <div class="balcao-cupom">

        {{-- O cupom, saindo da impressora --}}
        <div>
            <div class="impressora" aria-hidden="true"><span class="led"></span></div>

            <div class="cupom {{ session('imprimir') ? 'imprimindo' : '' }} {{ $venda->estaCancelada() ? 'cancelado' : '' }} {{ session('carimbar') ? 'tranco' : '' }}">

                {{-- Carimbo de cancelada --}}
                @if ($venda->estaCancelada())
                    <div class="carimbo {{ session('carimbar') ? 'batendo' : '' }}">CANCELADA</div>
                @endif

                <div class="centro">
                    <div class="loja">{{ config('app.name') }}</div>
                    <div class="aviso">Comprovante de venda · não é documento fiscal</div>
                </div>

                <hr class="corte">

                <div class="d-flex justify-content-between">
                    <span>VENDA Nº {{ str_pad($venda->id, 6, '0', STR_PAD_LEFT) }}</span>
                    <span>{{ $venda->data->format('d/m/Y') }}</span>
                </div>
                <div>CLIENTE: {{ $venda->cliente->nome }}</div>
                @if ($venda->cliente->cpf_cnpj)
                    <div>CPF/CNPJ: {{ $venda->cliente->cpf_cnpj }}</div>
                @endif

                <hr class="corte">

                {{-- Itens: nome em cima, conta embaixo com pontilhado até o subtotal --}}
                @foreach ($venda->itens as $item)
                    <div class="item-nome">{{ $loop->iteration }}. {{ $item->produto->nome }}</div>
                    <div class="item-conta">
                        <span>{{ $item->quantidade }} x {{ number_format($item->preco_unitario, 2, ',', '.') }}</span>
                        <span class="pontilhado"></span>
                        <span>{{ number_format($item->subtotal, 2, ',', '.') }}</span>
                    </div>
                @endforeach

                <hr class="corte">

                <div class="total">
                    <span>TOTAL</span>
                    <span class="valor-total">R$ {{ number_format($venda->total, 2, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between aviso mt-1">
                    <span>{{ $venda->itens->sum('quantidade') }} item(ns)</span>
                    <span>Operador: {{ $venda->usuario->name ?? '-' }} · {{ $venda->created_at->format('H:i') }}</span>
                </div>

                @if ($venda->observacao)
                    <hr class="corte">
                    <div>OBS: {{ $venda->observacao }}</div>
                @endif

                <div class="codigo-barras" aria-hidden="true">
                    @foreach ($barras as [$largura, $espaco])
                        <i style="width: {{ $largura * 2 }}px"></i><b style="width: {{ $espaco * 2 }}px"></b>
                    @endforeach
                </div>
                <div class="centro aviso">{{ implode(' ', str_split(implode('', $digitos), 4)) }}</div>
                <div class="centro mt-2">*** OBRIGADO PELA PREFERÊNCIA ***</div>
            </div>
        </div>

        {{-- Ao lado do cupom: ações e resumo --}}
        <div class="no-print">
            <div class="rotulo mb-2">Ações</div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir cupom</button>

                {{-- Cancelar devolve os produtos ao estoque --}}
                @unless ($venda->estaCancelada())
                    <form action="{{ route('vendas.cancelar', $venda) }}" method="POST"
                          data-confirmar="Cancelar a venda #{{ $venda->id }}? Os produtos voltarão para o estoque.">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Cancelar venda</button>
                    </form>
                @endunless
            </div>

            <div class="card">
                <div class="card-header">Resumo</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5 rotulo">Cliente</dt>
                        <dd class="col-7">{{ $venda->cliente->nome }}</dd>
                        <dt class="col-5 rotulo">Data</dt>
                        <dd class="col-7 numero">{{ $venda->data->format('d/m/Y') }}</dd>
                        <dt class="col-5 rotulo">Registrada por</dt>
                        <dd class="col-7">{{ $venda->usuario->name ?? '-' }} em <span class="numero">{{ $venda->created_at->format('d/m/Y H:i') }}</span></dd>
                        <dt class="col-5 rotulo">Situação</dt>
                        <dd class="col-7 mb-0">{{ $venda->estaCancelada() ? 'Cancelada (estoque devolvido)' : 'Concluída' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection
