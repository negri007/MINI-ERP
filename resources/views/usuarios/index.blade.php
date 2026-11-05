@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <div class="cabeca-lista">
        <h1 class="h3">Usuários <span class="qtd">{{ $aprovados->count() }} com acesso · {{ $pendentes->count() }} aguardando</span></h1>
    </div>
    @include('partials.dica', ['chave' => 'usuarios', 'texto' => 'Quem cria conta na página inicial aparece aqui e só entra depois de aprovado.'])

    {{-- Pedidos de acesso --}}
    <div class="card mb-4">
        <div class="card-header"><h2 class="titulo-cartao">Aguardando aprovação</h2></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Pediu acesso em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendentes as $usuario)
                        <tr>
                            <td class="fw-semibold">{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <form action="{{ route('usuarios.aprovar', $usuario) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">Aprovar</button>
                                    </form>
                                    <form action="{{ route('usuarios.recusar', $usuario) }}" method="POST"
                                          data-confirmar="Recusar o pedido de {{ $usuario->name }}? O cadastro é apagado." data-confirmar-titulo="Recusar pedido?" data-confirmar-sim="Recusar" data-confirmar-nao="Voltar">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Recusar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Nenhum pedido de acesso no momento.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quem já tem acesso --}}
    <div class="card">
        <div class="card-header"><h2 class="titulo-cartao">Com acesso</h2></div>
        <div class="card-body p-0 table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Aprovado em</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($aprovados as $usuario)
                        <tr>
                            <td class="fw-semibold">{{ $usuario->name }} @if ($usuario->is(auth()->user()))<span class="badge bg-secondary">você</span>@endif</td>
                            <td>{{ $usuario->email }}</td>
                            <td>{{ $usuario->aprovado_em->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
