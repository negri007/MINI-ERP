@extends('layouts.app')

@section('title', 'Novo cliente')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Clientes', 'rota' => 'clientes.index', 'titulo' => 'Novo cliente', 'chaveDica' => 'cliente-form', 'dica' => 'Só o nome é obrigatório. O CPF/CNPJ evita cadastro repetido.'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf

                @include('clientes._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
