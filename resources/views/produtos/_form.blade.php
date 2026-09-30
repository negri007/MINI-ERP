{{-- Campos do formulário, usados tanto no create quanto no edit --}}

{{-- Campo: Nome --}}
<div class="mb-3">
    <label for="nome" class="form-label">Nome <span class="text-danger" aria-hidden="true">*</span></label>
    <input type="text" name="nome" id="nome" aria-required="true" class="form-control @error('nome') is-invalid @enderror" @error('nome') aria-invalid="true" aria-describedby="nome-erro" @enderror value="{{ old('nome', $produto->nome) }}">
    @error('nome')
        <div class="invalid-feedback" id="nome-erro">{{ $message }}</div>
    @enderror
</div>

{{-- Campo: Descrição --}}
<div class="mb-3">
    <label for="descricao" class="form-label">Descrição</label>
    <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror" @error('descricao') aria-invalid="true" aria-describedby="descricao-erro" @enderror>{{ old('descricao', $produto->descricao) }}</textarea>
    @error('descricao')
        <div class="invalid-feedback" id="descricao-erro">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    {{-- Campo: Preço --}}
    <div class="col-md-4 mb-3">
        <label for="preco" class="form-label">Preço (R$) <span class="text-danger" aria-hidden="true">*</span></label>
        <input type="number" step="0.01" min="0" name="preco" id="preco" aria-required="true" class="form-control @error('preco') is-invalid @enderror" @error('preco') aria-invalid="true" aria-describedby="preco-erro" @enderror value="{{ old('preco', $produto->preco) }}">
        @error('preco')
            <div class="invalid-feedback" id="preco-erro">{{ $message }}</div>
        @enderror
    </div>

    {{-- Campo: Estoque (só no cadastro; depois muda por vendas e movimentações) --}}
    <div class="col-md-4 mb-3">
        @if ($produto->exists)
            <label class="form-label">Estoque atual</label>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ $produto->estoque }}" disabled>
                <a href="{{ route('estoque.create', ['produto_id' => $produto->id]) }}" class="btn btn-outline-secondary">Movimentar</a>
            </div>
            <div class="form-text">O estoque muda pelas vendas ou pela tela de Estoque.</div>
        @else
            <label for="estoque" class="form-label">Estoque inicial <span class="text-danger" aria-hidden="true">*</span></label>
            <input type="number" step="1" min="0" name="estoque" id="estoque" aria-required="true" class="form-control @error('estoque') is-invalid @enderror" @error('estoque') aria-invalid="true" aria-describedby="estoque-erro" @enderror value="{{ old('estoque', 0) }}">
            @error('estoque')
                <div class="invalid-feedback" id="estoque-erro">{{ $message }}</div>
            @enderror
        @endif
    </div>

    {{-- Campo: Estoque mínimo (abaixo disso o produto aparece no alerta do Dashboard) --}}
    <div class="col-md-4 mb-3">
        <div class="rotulo-com-ajuda">
            <label for="estoque_minimo" class="form-label">Estoque mínimo <span class="text-danger" aria-hidden="true">*</span></label>
            @include('partials.ajuda-campo', ['id' => 'ajuda-estoque-minimo', 'campo' => 'estoque mínimo'])
        </div>
        <input type="number" step="1" min="0" name="estoque_minimo" id="estoque_minimo" aria-required="true" class="form-control @error('estoque_minimo') is-invalid @enderror" @error('estoque_minimo') aria-invalid="true" aria-describedby="ajuda-estoque-minimo estoque_minimo-erro" @else aria-describedby="ajuda-estoque-minimo" @enderror value="{{ old('estoque_minimo', $produto->estoque_minimo) }}">
        <p class="ajuda-texto" id="ajuda-estoque-minimo" hidden>Quando o estoque chegar neste número, o produto aparece em "Para repor". Não impede a venda.</p>
        @error('estoque_minimo')
            <div class="invalid-feedback" id="estoque_minimo-erro">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    {{-- Campo: Categoria --}}
    <div class="col-md-6 mb-3">
        <div class="rotulo-com-acao">
            <label for="categoria_id" class="form-label">Categoria <span class="text-danger" aria-hidden="true">*</span></label>
            {{-- Com JavaScript abre o modal; sem JavaScript vai para o cadastro normal de categoria --}}
            <a href="{{ route('categorias.create') }}" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCategoria">+ Nova categoria</a>
        </div>
        <select name="categoria_id" id="categoria_id" aria-required="true" class="form-select @error('categoria_id') is-invalid @enderror" @error('categoria_id') aria-invalid="true" aria-describedby="categoria_id-erro" @enderror>
            <option value="">Selecione...</option>
            @foreach ($categorias as $categoria)
                <option value="{{ $categoria->id }}" @selected(old('categoria_id', $produto->categoria_id) == $categoria->id)>{{ $categoria->nome }}</option>
            @endforeach
        </select>
        @error('categoria_id')
            <div class="invalid-feedback" id="categoria_id-erro">{{ $message }}</div>
        @enderror
        @if ($categorias->isEmpty())
            <p class="form-text mb-0" id="semCategoria">Nenhuma categoria ainda. Crie a primeira em "+ Nova categoria".</p>
        @endif
    </div>

    {{-- Campo: Fornecedor (opcional) --}}
    <div class="col-md-6 mb-3">
        <label for="fornecedor_id" class="form-label">Fornecedor</label>
        <select name="fornecedor_id" id="fornecedor_id" class="form-select @error('fornecedor_id') is-invalid @enderror" @error('fornecedor_id') aria-invalid="true" aria-describedby="fornecedor_id-erro" @enderror>
            <option value="">Nenhum</option>
            @foreach ($fornecedores as $fornecedor)
                <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $produto->fornecedor_id) == $fornecedor->id)>{{ $fornecedor->nome }}</option>
            @endforeach
        </select>
        @error('fornecedor_id')
            <div class="invalid-feedback" id="fornecedor_id-erro">{{ $message }}</div>
        @enderror
    </div>
</div>
