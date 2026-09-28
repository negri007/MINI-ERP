@extends('layouts.app')

@section('title', 'Novo Cliente')

@section('content')
    <h1 class="h3 mb-3">Novo Cliente</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('clientes.store') }}" method="POST">
                @csrf

                @include('clientes._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
