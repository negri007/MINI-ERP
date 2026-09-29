@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Clientes', 'rota' => 'clientes.index', 'titulo' => 'Editar cliente', 'chaveDica' => 'cliente-form', 'dica' => 'Só o nome é obrigatório. O CPF/CNPJ evita cadastro repetido.'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')

                @include('clientes._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
