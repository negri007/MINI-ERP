@extends('layouts.app')

@section('title', 'Nova Categoria')

@section('content')
    <h1 class="h3 mb-3">Nova Categoria</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('categorias.store') }}" method="POST">
                @csrf

                @include('categorias._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
