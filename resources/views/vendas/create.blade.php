@extends('layouts.app')

@section('title', 'Nova venda')

@section('content')
    @include('partials.cabeca-form', ['lista' => 'Vendas', 'rota' => 'vendas.index', 'titulo' => 'Nova venda', 'chaveDica' => 'venda-form', 'dica' => 'Não achou o cliente? Cadastre sem sair daqui em "+ Novo cliente".'])

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

    @if ($produtos->isEmpty())
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2">
            @if ($esgotados->isEmpty())
                <span>Nenhum produto cadastrado. Cadastre o que você vende para começar.</span>
                <a href="{{ route('produtos.create') }}" class="btn btn-sm btn-outline-primary">Cadastrar produto</a>
            @else
                <span>Nenhum produto com estoque. Registre uma entrada para começar a vender.</span>
                <a href="{{ route('estoque.create') }}" class="btn btn-sm btn-outline-primary">Movimentar estoque</a>
            @endif
        </div>
    @endif

    <form action="{{ route('vendas.store') }}" method="POST" id="formVenda">
        @csrf

        {{-- Cabeçalho da venda --}}
        <div class="card mb-3">
            <div class="card-body row">
                <div class="col-md-6 mb-3">
                    <div class="rotulo-com-acao">
                        <label for="cliente_id" class="form-label">Cliente <span class="text-danger">*</span></label>
                        {{-- Com JavaScript abre o modal; sem JavaScript vai para o cadastro normal --}}
                        <a href="{{ route('clientes.create') }}" class="btn btn-sm btn-outline-secondary" id="btnNovoCliente"
                           data-bs-toggle="modal" data-bs-target="#modalCliente">+ Novo cliente</a>
                    </div>

                    {{-- Reserva: <select> comum. Se o JavaScript carregar, ele fica escondido e a busca abaixo
                         aparece no lugar; o valor escolhido continua sendo enviado por este campo. --}}
                    <select name="cliente_id" id="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror">
                        <option value="">Selecione...</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" data-nome="{{ $cliente->nome }}" data-documento="{{ $cliente->cpf_cnpj }}"
                                    data-consumidor-final="{{ $cliente->ehConsumidorFinal() ? 1 : 0 }}" @selected(old('cliente_id') == $cliente->id)>
                                {{ $cliente->nome }}{{ $cliente->cpf_cnpj ? " · {$cliente->cpf_cnpj}" : '' }}{{ $cliente->ehConsumidorFinal() ? ' (venda sem identificar o cliente)' : '' }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Busca enquanto digita (padrão "combobox" do ARIA) --}}
                    <div class="combo" id="comboCliente" data-url="{{ route('clientes.buscar') }}" hidden>
                        <input type="text" id="clienteBusca" class="form-control @error('cliente_id') is-invalid @enderror" role="combobox"
                               aria-autocomplete="list" aria-expanded="false" aria-controls="clienteOpcoes"
                               aria-describedby="clienteErro" autocomplete="off" placeholder="Ex.: Maria ou 123.456.789-09">
                        <ul class="combo-lista" id="clienteOpcoes" role="listbox" aria-label="Clientes encontrados" hidden></ul>
                        <div class="combo-vazio" id="clienteVazio" hidden>
                            <span></span>
                            <button type="button" class="btn btn-sm btn-outline-primary">Cadastrar</button>
                        </div>
                    </div>
                    <div class="invalid-feedback d-block" id="clienteErro">@error('cliente_id'){{ $message }}@enderror</div>
                    {{-- Avisos para o leitor de tela (quantos clientes apareceram, qual foi escolhido) --}}
                    <div class="visually-hidden" id="clienteAviso" aria-live="polite"></div>
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
            @if ($esgotados->isNotEmpty())
                <p class="form-text px-3 pb-3 mb-0">
                    {{ $esgotados->count() === 1 ? '1 produto sem estoque aparece' : $esgotados->count().' produtos sem estoque aparecem' }}
                    no fim da lista, sem poder escolher. <a href="{{ route('estoque.create') }}">Registrar entrada</a>
                </p>
            @endif
        </div>

        {{-- Botões: rodapé do formulário (Cancelar à esquerda, ação principal em amarelo) --}}
        <div class="pe-form">
            <a href="{{ route('vendas.index') }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Registrar venda</button>
        </div>
    </form>

    {{-- Modal "Novo cliente": cadastro rápido sem sair da venda (os itens continuam na tela).
         O Bootstrap prende o foco dentro dele, fecha no Esc e devolve o foco ao botão. --}}
    <div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="modalClienteTitulo" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="formClienteRapido" action="{{ route('clientes.rapido') }}" method="POST" novalidate data-cadastro-rapido>
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title h5" id="modalClienteTitulo">Novo cliente</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" data-erro-geral hidden></div>
                    <div class="mb-3">
                        <label for="rapido_nome" class="form-label">Nome <span class="text-danger">*</span></label>
                        <input type="text" name="nome" id="rapido_nome" class="form-control" autocomplete="off" aria-describedby="rapido_nome_erro" required>
                        <div class="invalid-feedback" id="rapido_nome_erro" data-erro="nome"></div>
                    </div>
                    <div class="mb-3">
                        <div class="rotulo-com-ajuda">
                            <label for="rapido_cpf_cnpj" class="form-label">CPF/CNPJ</label>
                            @include('partials.ajuda-campo', ['id' => 'ajuda-rapido-cpf-cnpj', 'campo' => 'o campo CPF/CNPJ'])
                        </div>
                        <input type="text" name="cpf_cnpj" id="rapido_cpf_cnpj" class="form-control" data-mascara="cpfcnpj" inputmode="numeric"
                               placeholder="000.000.000-00" aria-describedby="rapido_cpf_cnpj_erro ajuda-rapido-cpf-cnpj">
                        <p class="ajuda-texto" id="ajuda-rapido-cpf-cnpj" hidden>Pode digitar só os números; a máscara entra sozinha. Não é obrigatório.</p>
                        <div class="invalid-feedback" id="rapido_cpf_cnpj_erro" data-erro="cpf_cnpj"></div>
                    </div>
                    <div>
                        <label for="rapido_telefone" class="form-label">Telefone</label>
                        <input type="tel" name="telefone" id="rapido_telefone" class="form-control" data-mascara="telefone" inputmode="numeric"
                               placeholder="(00) 00000-0000" aria-describedby="rapido_telefone_erro">
                        <div class="invalid-feedback" id="rapido_telefone_erro" data-erro="telefone"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn btn-primary">Cadastrar cliente</button>
                </div>
            </form>
        </div>
    </div>

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
                    {{-- Sem estoque: aparecem para a pessoa entender por que não dá para escolher --}}
                    @if ($esgotados->isNotEmpty())
                        <optgroup label="Sem estoque (registre uma entrada para vender)">
                            @foreach ($esgotados as $produto)
                                <option disabled>{{ $produto->nome }} — sem estoque</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </td>
            <td>
                <input type="number" min="1" value="1" class="form-control campo-quantidade" data-nome="quantidade" aria-label="Quantidade">
                <div class="invalid-feedback aviso-estoque"></div>
            </td>
            <td class="text-end text-nowrap preco">-</td>
            <td class="text-end text-nowrap subtotal">-</td>
            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btn-remover" aria-label="Remover item">&times;</button></td>
        </tr>
    </template>
@endsection

@push('scripts')
<script src="{{ asset('js/cadastro-rapido.js') }}"></script>
<script src="{{ asset('js/combo-cliente.js') }}"></script>
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

        // Aviso de estoque: ligado à quantidade pelo aria-describedby
        const campoQtd = linha.querySelector('.campo-quantidade');
        const aviso = linha.querySelector('.aviso-estoque');
        aviso.id = `aviso-estoque-${indice}`;
        campoQtd.setAttribute('aria-describedby', aviso.id);

        linha.addEventListener('input', calcular);
        linha.addEventListener('change', calcular);
        // Confere o estoque ao sair do campo ou trocar o produto; ao corrigir, o aviso some
        campoQtd.addEventListener('blur', () => conferirEstoque(linha));
        linha.querySelector('.campo-produto').addEventListener('change', () => conferirEstoque(linha));
        campoQtd.addEventListener('input', () => { if (campoQtd.classList.contains('is-invalid')) conferirEstoque(linha); });
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

    // Mostra "Só há N em estoque." abaixo da quantidade (o servidor confere de novo ao salvar)
    function conferirEstoque(linha) {
        const opcao = linha.querySelector('.campo-produto').selectedOptions[0];
        const campoQtd = linha.querySelector('.campo-quantidade');
        const aviso = linha.querySelector('.aviso-estoque');
        const estoque = parseInt(opcao?.dataset.estoque || 0);
        const passou = estoque > 0 && parseInt(campoQtd.value || 0) > estoque;

        campoQtd.classList.toggle('is-invalid', passou);
        campoQtd.toggleAttribute('aria-invalid', passou);
        aviso.textContent = passou ? `Só há ${estoque} em estoque.` : '';
    }

    document.getElementById('btnAdicionarItem').addEventListener('click', () => adicionarItem());

    // Recria os itens enviados (se a validação falhou) ou começa com uma linha vazia
    const itensAntigos = @json(array_values(old('itens', [])));
    (itensAntigos.length ? itensAntigos : [{}]).forEach(adicionarItem);
</script>
@endpush
