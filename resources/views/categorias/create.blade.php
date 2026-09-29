@extends('layouts.app')

@section('title', 'Nova categoria')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Categorias', 'rota' => 'categorias.index', 'titulo' => 'Nova categoria'])

    {{-- Formulário --}}
    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf

                @include('categorias._form')

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
@endsection
