@extends('layouts.app')

@section('title', 'Nova venda')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Vendas', 'rota' => 'vendas.index', 'titulo' => 'Nova venda'])

    {{-- Erros gerais (ex.: estoque insuficiente, venda sem itens) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($clientes->isEmpty() || $produtos->isEmpty())
        <div class="alert alert-warning">
            Para vender é preciso ter pelo menos um <a href="{{ route('clientes.create') }}">cliente</a>
            e um <a href="{{ route('produtos.create') }}">produto com estoque</a>.
        </div>
    @endif

    {{-- form-venda: mesma largura de leitura dos outros formulários --}}
    <form action="{{ route('vendas.store') }}" method="POST" id="formVenda" class="form-venda">
        @csrf

        {{-- Cabeçalho da venda --}}
        <div class="card mb-3">
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label for="cliente_id" class="form-label">Cliente <span class="text-danger" aria-hidden="true">*</span></label>
                    <select name="cliente_id" id="cliente_id" aria-required="true" class="form-select @error('cliente_id') is-invalid @enderror" @error('cliente_id') aria-invalid="true" aria-describedby="cliente_id-erro" @enderror>
                        <option value="">Selecione...</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                {{ $cliente->nome }}{{ $cliente->cpf_cnpj ? " ({$cliente->cpf_cnpj})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <div class="invalid-feedback" id="cliente_id-erro">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data" class="form-label">Data <span class="text-danger" aria-hidden="true">*</span></label>
                    <input type="date" name="data" id="data" aria-required="true" class="form-control @error('data') is-invalid @enderror" @error('data') aria-invalid="true" aria-describedby="data-erro" @enderror value="{{ old('data', now()->toDateString()) }}">
                    @error('data')
                        <div class="invalid-feedback" id="data-erro">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label for="observacao" class="form-label">Observação</label>
                    <textarea name="observacao" id="observacao" rows="2" class="form-control">{{ old('observacao') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Itens da venda: linhas adicionadas pelo JavaScript abaixo --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="titulo-cartao">Itens</h2>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAdicionarItem">+ Adicionar produto</button>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50%">Produto</th>
                            <th style="width: 15%">Quantidade</th>
                            <th class="text-end">Preço</th>
                            <th class="text-end">Subtotal</th>
                            <th><span class="visually-hidden">Remover</span></th>
                        </tr>
                    </thead>
                    <tbody id="itens"></tbody>
                </table>
            </div>
        </div>

        {{-- Rodapé da venda: o total fica grande ao lado do botão de registrar e acompanha a rolagem
             (sticky), para o total nunca sumir quando a venda tem muitos itens.
             aria-live: o leitor de tela anuncia o total novo quando ele muda. --}}
        <div class="pe-form pe-venda">
            <div class="total-venda">
                <small>Total da venda</small>
                <strong id="totalVenda" aria-live="polite">R$ 0,00</strong>
            </div>
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar venda</button>
        </div>
    </form>

    {{-- Modelo de uma linha de item (copiado pelo JavaScript) --}}
    <template id="modeloItem">
        <tr>
            <td>
                <select class="form-select campo-produto" data-nome="produto_id" aria-label="Produto">
                    <option value="">Selecione...</option>
                    @foreach ($produtos as $produto)
                        <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}" data-estoque="{{ $produto->estoque }}">
                            {{ $produto->nome }} — estoque: {{ $produto->estoque }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" min="1" value="1" class="form-control campo-quantidade" data-nome="quantidade" aria-label="Quantidade"></td>
            <td class="text-end text-nowrap preco">-</td>
            <td class="text-end text-nowrap subtotal">-</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remover" aria-label="Remover item"><span aria-hidden="true">&times;</span></button></td>
        </tr>
    </template>
@endsection

@push('scripts')
<script>
    // Monta as linhas de itens da venda e calcula os totais
    const corpo = document.getElementById('itens');
    const modelo = document.getElementById('modeloItem');
    const moeda = (valor) => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    let contador = 0;

    // Cria uma linha; "item" vem do old() quando a validação falha
    function adicionarItem(item = {}) {
        const linha = modelo.content.firstElementChild.cloneNode(true);
        const indice = contador++;

        // Dá o nome itens[0][produto_id], itens[0][quantidade] ... para o Laravel receber como lista
        linha.querySelectorAll('[data-nome]').forEach((campo) => {
            campo.name = `itens[${indice}][${campo.dataset.nome}]`;
        });

        if (item.produto_id) linha.querySelector('.campo-produto').value = item.produto_id;
        if (item.quantidade) linha.querySelector('.campo-quantidade').value = item.quantidade;

        linha.addEventListener('input', calcular);
        linha.addEventListener('change', calcular);
        linha.querySelector('.btn-remover').addEventListener('click', () => {
            linha.remove();
            calcular();
            // O botão clicado sumiu: o foco vai para "Adicionar produto" em vez de se perder
            document.getElementById('btnAdicionarItem').focus();
        });

        corpo.appendChild(linha);
        calcular();
        return linha;
    }

    // Dá nome a cada campo pelo número do item ("Produto do item 2"), para o leitor de tela
    function numerarItens() {
        corpo.querySelectorAll('tr').forEach((linha, i) => {
            const n = i + 1;
            linha.querySelector('.campo-produto').setAttribute('aria-label', `Produto do item ${n}`);
            linha.querySelector('.campo-quantidade').setAttribute('aria-label', `Quantidade do item ${n}`);
            linha.querySelector('.btn-remover').setAttribute('aria-label', `Remover item ${n}`);
        });
    }

    // Recalcula preço, subtotal de cada linha e o total da venda
    function calcular() {
        let total = 0;
        corpo.querySelectorAll('tr').forEach((linha) => {
            const opcao = linha.querySelector('.campo-produto').selectedOptions[0];
            const campoQtd = linha.querySelector('.campo-quantidade');
            const preco = parseFloat(opcao?.dataset.preco || 0);
            const quantidade = parseInt(campoQtd.value || 0);

            // Não deixa digitar mais que o estoque disponível
            if (opcao?.dataset.estoque) campoQtd.max = opcao.dataset.estoque;

            const subtotal = preco * quantidade;
            total += subtotal;
            linha.querySelector('.preco').textContent = preco ? moeda(preco) : '-';
            linha.querySelector('.subtotal').textContent = preco ? moeda(subtotal) : '-';
        });
        document.getElementById('totalVenda').textContent = moeda(total);
        numerarItens();
    }

    // Ao adicionar pelo botão, o foco já vai para o produto da linha nova
    document.getElementById('btnAdicionarItem').addEventListener('click', () => {
        adicionarItem().querySelector('.campo-produto').focus();
    });

    // Recria os itens enviados (se a validação falhou) ou começa com uma linha vazia
    const itensAntigos = @json(array_values(old('itens', [])));
    (itensAntigos.length ? itensAntigos : [{}]).forEach(adicionarItem);
</script>
@endpush
