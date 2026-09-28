@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
    <h1 class="h3 mb-3">Editar Cliente</h1>

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('clientes.update', $cliente) }}" method="POST">
                @csrf
                @method('PUT')

                @include('clientes._form')

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
