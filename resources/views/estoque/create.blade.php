@extends('layouts.app')

@section('title', 'Movimentar Estoque')

@section('content')
    <h1 class="h3 mb-3">Movimentar Estoque</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('estoque.store') }}" method="POST">
                @csrf

                {{-- Campo: Produto --}}
                <div class="mb-3">
                    <label for="produto_id" class="form-label">Produto <span class="text-danger">*</span></label>
                    <select name="produto_id" id="produto_id" class="form-select @error('produto_id') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach ($produtos as $produto)
                            <option value="{{ $produto->id }}" @selected(old('produto_id', $produtoSelecionado) == $produto->id)>
                                {{ $produto->nome }} — estoque atual: {{ $produto->estoque }}
                            </option>
                        @endforeach
                    </select>
                    @error('produto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    {{-- Campo: Tipo --}}
                    <div class="col-md-6 mb-3">
                        <label class="form-label d-block">Tipo <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="entrada" @checked(old('tipo', 'entrada') === 'entrada')>
                            <label class="form-check-label" for="tipo_entrada">Entrada (compra, devolução...)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo" id="tipo_saida" value="saida" @checked(old('tipo') === 'saida')>
                            <label class="form-check-label" for="tipo_saida">Saída (perda, avaria...)</label>
                        </div>
                        @error('tipo')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campo: Quantidade --}}
                    <div class="col-md-6 mb-3">
                        <label for="quantidade" class="form-label">Quantidade <span class="text-danger">*</span></label>
                        <input type="number" min="1" step="1" name="quantidade" id="quantidade" class="form-control @error('quantidade') is-invalid @enderror" value="{{ old('quantidade') }}">
                        @error('quantidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Campo: Motivo --}}
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                    <input type="text" name="motivo" id="motivo" class="form-control @error('motivo') is-invalid @enderror" value="{{ old('motivo') }}" placeholder="Ex.: Compra NF 1234 do fornecedor X">
                    @error('motivo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">Registrar</button>
                <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
