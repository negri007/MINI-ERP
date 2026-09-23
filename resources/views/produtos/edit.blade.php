@extends('layouts.app')

@section('title', 'Editar Produto')

@section('content')
    <h1 class="h3 mb-3">Editar Produto</h1>

    {{-- Aviso caso ainda não existam categorias --}}
    @if ($categorias->isEmpty())
        <div class="alert alert-warning">
            Cadastre uma <a href="{{ route('categorias.create') }}">categoria</a> antes de cadastrar produtos.
        </div>
    @endif

    {{-- Formulário --}}
    <div class="card">
        <div class="card-body">
            <form action="{{ route('produtos.update', $produto) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Campo: Nome --}}
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome <span class="text-danger">*</span></label>
                    <input type="text" name="nome" id="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $produto->nome) }}">
                    @error('nome')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Campo: Descrição --}}
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror">{{ old('descricao', $produto->descricao) }}</textarea>
                    @error('descricao')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    {{-- Campo: Preço --}}
                    <div class="col-md-6 mb-3">
                        <label for="preco" class="form-label">Preço (R$) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="preco" id="preco" class="form-control @error('preco') is-invalid @enderror" value="{{ old('preco', $produto->preco) }}">
                        @error('preco')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo: Estoque --}}
                    <div class="col-md-6 mb-3">
                        <label for="estoque" class="form-label">Estoque <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="estoque" id="estoque" class="form-control @error('estoque') is-invalid @enderror" value="{{ old('estoque', $produto->estoque) }}">
                        @error('estoque')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    {{-- Campo: Categoria --}}
                    <div class="col-md-6 mb-3">
                        <label for="categoria_id" class="form-label">Categoria <span class="text-danger">*</span></label>
                        <select name="categoria_id" id="categoria_id" class="form-select @error('categoria_id') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected(old('categoria_id', $produto->categoria_id) == $categoria->id)>{{ $categoria->nome }}</option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo: Fornecedor (opcional) --}}
                    <div class="col-md-6 mb-3">
                        <label for="fornecedor_id" class="form-label">Fornecedor</label>
                        <select name="fornecedor_id" id="fornecedor_id" class="form-select @error('fornecedor_id') is-invalid @enderror">
                            <option value="">Nenhum</option>
                            @foreach ($fornecedores as $fornecedor)
                                <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $produto->fornecedor_id) == $fornecedor->id)>{{ $fornecedor->nome }}</option>
                            @endforeach
                        </select>
                        @error('fornecedor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Botões --}}
                <button type="submit" class="btn btn-success">Atualizar</button>
                <a href="{{ route('produtos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
