@extends('layouts.app')

@section('title', 'Novo fornecedor')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Fornecedores', 'rota' => 'fornecedores.index', 'titulo' => 'Novo fornecedor'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('fornecedores.store') }}" method="POST">
                @csrf

                @include('fornecedores._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
