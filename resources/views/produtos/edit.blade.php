@extends('layouts.app')

@section('title', 'Editar produto')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Produtos', 'rota' => 'produtos.index', 'titulo' => 'Editar produto'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('produtos.update', $produto) }}" method="POST">
                @csrf
                @method('PUT')

                @include('produtos._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
