@extends('layouts.app')

@section('title', 'Novo Produto')

@section('content')
    <h1 class="h3 mb-3">Novo Produto</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('produtos.store') }}" method="POST">
                @csrf

                @include('produtos._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
