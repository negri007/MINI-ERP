@extends('layouts.app')

@section('title', 'Novo Fornecedor')

@section('content')
    <h1 class="h3 mb-3">Novo Fornecedor</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('fornecedores.store') }}" method="POST">
                @csrf

                @include('fornecedores._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
