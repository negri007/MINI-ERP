@extends('layouts.app')

@section('title', 'Movimentar estoque')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Estoque', 'rota' => 'estoque.index', 'titulo' => 'Movimentar estoque', 'chaveDica' => 'estoque-form', 'dica' => 'Entrada = mercadoria que chegou. Saída = perda, quebra ou uso próprio. Vendas baixam o estoque sozinhas.'])

    <div class="card cartao-form">
        <div class="card-body">
            <form action="{{ route('estoque.store') }}" method="POST">
                @csrf

                {{-- Campo: Produto --}}
                <div class="mb-3">
                    <label for="produto_id" class="form-label">Produto <span class="text-danger">*</span></label>
                    <select name="produto_id" id="produto_id" class="form-select @error('produto_id') is-invalid @enderror" aria-describedby="estoqueAtual">
                        <option value="">Selecione...</option>
                        @foreach ($produtos as $produto)
                            <option value="{{ $produto->id }}" data-estoque="{{ $produto->estoque }}" @selected(old('produto_id', $produtoSelecionado) == $produto->id)>
                                {{ $produto->nome }} — estoque atual: {{ $produto->estoque }}
                            </option>
                        @endforeach
                    </select>
                    @error('produto_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    {{-- Estoque atual do produto escolhido (lido pelo leitor de tela ao mudar) --}}
                    <p class="form-text mb-0" id="estoqueAtual" aria-live="polite"></p>
                </div>

                <div class="row">
                    {{-- Campo: Tipo --}}
                    <fieldset class="col-md-6 mb-3" aria-describedby="ajuda-tipo">
                        {{-- O legend precisa ser o primeiro filho do fieldset: é ele que dá nome ao grupo --}}
                        <legend class="form-label legenda-com-ajuda">Tipo <span class="text-danger">*</span></legend>
                        @include('partials.ajuda-campo', ['id' => 'ajuda-tipo', 'campo' => 'o tipo de movimentação'])
                        <div class="mt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="entrada" @checked(old('tipo', 'entrada') === 'entrada')>
                                <label class="form-check-label" for="tipo_entrada">Entrada (compra, devolução...)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_saida" value="saida" @checked(old('tipo') === 'saida')>
                                <label class="form-check-label" for="tipo_saida">Saída (perda, avaria...)</label>
                            </div>
                        </div>
                        <p class="ajuda-texto" id="ajuda-tipo" hidden>Entrada soma ao estoque: mercadoria que chegou ou devolução. Saída tira do estoque: perda, quebra ou uso próprio. Vendas já baixam o estoque sozinhas, não registre aqui.</p>
                        @error('tipo')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </fieldset>

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

                {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
                <div class="pe-form">
                    <a href="{{ route('estoque.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Mostra o estoque atual do produto escolhido, para a saída não passar do que existe
    const campoProduto = document.getElementById('produto_id');
    const estoqueAtual = document.getElementById('estoqueAtual');
    const mostrarEstoque = () => {
        const opcao = campoProduto.selectedOptions[0];
        estoqueAtual.textContent = opcao?.value ? `Estoque atual: ${opcao.dataset.estoque} un.` : '';
    };
    campoProduto.addEventListener('change', mostrarEstoque);
    mostrarEstoque();
</script>
@endpush

