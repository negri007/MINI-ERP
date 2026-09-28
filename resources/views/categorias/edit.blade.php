@extends('layouts.app')

@section('title', 'Editar Categoria')

@section('content')
    <h1 class="h3 mb-3">Editar Categoria</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('categorias.update', $categoria) }}" method="POST">
                @csrf
                @method('PUT')

                @include('categorias._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('categorias.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
