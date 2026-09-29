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

    <form action="{{ route('vendas.store') }}" method="POST" id="formVenda">
        @csrf

        {{-- Cabeçalho da venda --}}
        <div class="card mb-3">
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                    <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                {{ $cliente->nome }}{{ $cliente->cpf_cnpj ? " ({$cliente->cpf_cnpj})" : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label for="data" class="form-label">Data <span class="text-danger">*</span></label>
                    <input type="date" name="data" id="data" class="form-control @error('data') is-invalid @enderror" value="{{ old('data', now()->toDateString()) }}">
                    @error('data')
                        <div class="invalid-feedback">{{ $message }}</div>
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
                <span>Itens</span>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="itens"></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total</th>
                            <th class="text-end fs-5" id="totalVenda">R$ 0,00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
        <div class="pe-form">
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar venda</button>
        </div>
    </form>

    {{-- Modelo de uma linha de item (copiado pelo JavaScript) --}}
    <template id="modeloItem">
        <tr>
            <td>
                <select class="form-select campo-produto" data-nome="produto_id">
                    <option value="">Selecione...</option>
                    @foreach ($produtos as $produto)
                        <option value="{{ $produto->id }}" data-preco="{{ $produto->preco }}" data-estoque="{{ $produto->estoque }}">
                            {{ $produto->nome }} — estoque: {{ $produto->estoque }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" min="1" value="1" class="form-control campo-quantidade" data-nome="quantidade"></td>
            <td class="text-end text-nowrap preco">-</td>
            <td class="text-end text-nowrap subtotal">-</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remover">&times;</button></td>
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
        linha.querySelector('.btn-remover').addEventListener('click', () => { linha.remove(); calcular(); });

        corpo.appendChild(linha);
        calcular();
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
    }

    document.getElementById('btnAdicionarItem').addEventListener('click', () => adicionarItem());

    // Recria os itens enviados (se a validação falhou) ou começa com uma linha vazia
    const itensAntigos = @json(array_values(old('itens', [])));
    (itensAntigos.length ? itensAntigos : [{}]).forEach(adicionarItem);
</script>
@endpush
