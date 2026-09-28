@extends('layouts.app')

@section('title', 'Editar Produto')

@section('content')
    <h1 class="h3 mb-3">Editar Produto</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('produtos.update', $produto) }}" method="POST">
                @csrf
                @method('PUT')

                @include('produtos._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
