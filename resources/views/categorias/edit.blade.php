@extends('layouts.app')

@section('title', 'Editar categoria')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Categorias', 'rota' => 'categorias.index', 'titulo' => 'Editar categoria', 'chaveDica' => 'categoria-form', 'dica' => 'Use um nome curto, como Bebidas ou Limpeza.'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('categorias.update', $categoria) }}" method="POST">
                @csrf
                @method('PUT')

                @include('categorias._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
