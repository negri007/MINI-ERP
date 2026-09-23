@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Dashboard</h1>

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

    {{-- Produtos com estoque baixo --}}
    <div class="card">
        <div class="card-header">Produtos com estoque baixo (até 5 unidades)</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th class="text-end">Estoque</th>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum produto com estoque baixo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
