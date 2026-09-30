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
                    <label for="produto_id" class="form-label">Produto <span class="text-danger" aria-hidden="true">*</span></label>
                    <select name="produto_id" id="produto_id" aria-required="true" class="form-select @error('produto_id') is-invalid @enderror" @error('produto_id') aria-invalid="true" aria-describedby="estoqueAtual produto_id-erro" @else aria-describedby="estoqueAtual" @enderror>
                        <option value="">Selecione...</option>
                        @foreach ($produtos as $produto)
                            <option value="{{ $produto->id }}" data-estoque="{{ $produto->estoque }}" @selected(old('produto_id', $produtoSelecionado) == $produto->id)>
                                {{ $produto->nome }} — estoque atual: {{ $produto->estoque }}
                            </option>
                        @endforeach
                    </select>
                    @error('produto_id')
                        <div class="invalid-feedback" id="produto_id-erro">{{ $message }}</div>
                    @enderror
                    {{-- Estoque atual do produto escolhido (lido pelo leitor de tela ao mudar) --}}
                    <p class="form-text mb-0" id="estoqueAtual" aria-live="polite"></p>
                </div>

                <div class="row">
                    {{-- Campo: Tipo (fieldset + legend: o leitor de tela lê "Tipo" junto de cada opção) --}}
                    <fieldset class="col-md-6 mb-3" @error('tipo') aria-describedby="ajuda-tipo tipo-erro" @else aria-describedby="ajuda-tipo" @enderror>
                        {{-- O legend precisa ser o primeiro filho do fieldset: é ele que dá nome ao grupo --}}
                        <legend class="form-label legenda-com-ajuda">Tipo <span class="text-danger" aria-hidden="true">*</span></legend>
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
                            <div class="text-danger small" id="tipo-erro">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    {{-- Campo: Quantidade --}}
                    <div class="col-md-6 mb-3">
                        <label for="quantidade" class="form-label">Quantidade <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="number" min="1" step="1" name="quantidade" id="quantidade" aria-required="true" class="form-control @error('quantidade') is-invalid @enderror" @error('quantidade') aria-invalid="true" aria-describedby="quantidade-erro" @enderror value="{{ old('quantidade') }}">
                        @error('quantidade')
                            <div class="invalid-feedback" id="quantidade-erro">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Campo: Motivo --}}
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo <span class="text-danger" aria-hidden="true">*</span></label>
                    <input type="text" name="motivo" id="motivo" aria-required="true" class="form-control @error('motivo') is-invalid @enderror" @error('motivo') aria-invalid="true" aria-describedby="motivo-erro" @enderror value="{{ old('motivo') }}" placeholder="Ex.: Compra NF 1234 do fornecedor X">
                    @error('motivo')
                        <div class="invalid-feedback" id="motivo-erro">{{ $message }}</div>
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

