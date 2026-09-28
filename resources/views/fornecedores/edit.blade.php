@extends('layouts.app')

@section('title', 'Editar Fornecedor')

@section('content')
    <h1 class="h3 mb-3">Editar Fornecedor</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('fornecedores.update', $fornecedor) }}" method="POST">
                @csrf
                @method('PUT')

                @include('fornecedores._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('fornecedores.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
