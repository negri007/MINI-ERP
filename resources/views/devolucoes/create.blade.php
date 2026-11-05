@extends('layouts.app')

@section('title', 'Registrar devolução')

@section('content')
    @php($numero = str_pad($venda->id, 4, '0', STR_PAD_LEFT))

    <nav class="trilha" aria-label="Você está em">
        <a href="{{ route('vendas.index') }}">Vendas</a>
        <span aria-hidden="true">›</span>
        <a href="{{ route('vendas.show', $venda) }}">Venda #{{ $numero }}</a>
        <span aria-hidden="true">›</span>
        <span aria-current="page">Devolução</span>
    </nav>
    <h1 class="h3 mb-1">Registrar devolução</h1>
    <p class="subtitulo">Venda #{{ $numero }} · {{ $venda->cliente->nome }} · {{ $venda->data->format('d/m/Y') }} · pago em {{ $venda->nomeFormaPagamento() }}</p>
    @include('partials.dica', ['chave' => 'devolucao-form', 'texto' => 'Digite quantas unidades voltaram. O valor já sai com o desconto da venda e só volta ao estoque se o destino for "Volta para o estoque".'])

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $erro)
                <div>{{ $erro }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('devolucoes.store', $venda) }}" method="POST" class="row g-4">
        @csrf

        {{-- Bloco 1: quais produtos e quantos --}}
        <div class="col-xl-7">
            <div class="card cartao-form h-100">
                <div class="card-body">
                    <h2 class="grupo-form">Produtos devolvidos</h2>
                    <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th class="text-center">Vendido</th>
                                <th class="text-center">Já voltou</th>
                                <th class="text-end">Preço un.</th>
                                <th class="text-end" style="width: 9rem">Devolver agora</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($venda->itens as $item)
                                @php($disponivel = $item->quantidadeDisponivelParaDevolver())
                                <tr>
                                    <td class="fw-semibold"><label for="item-{{ $item->id }}">{{ $item->produto->nome }}</label></td>
                                    <td class="text-center">{{ $item->quantidade }}</td>
                                    <td class="text-center">{{ $item->quantidadeDevolvida() }}</td>
                                    <td class="text-end">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        @if ($disponivel > 0)
                                            <input type="number" min="0" max="{{ $disponivel }}" step="1" name="itens[{{ $item->id }}]" id="item-{{ $item->id }}"
                                                   class="form-control text-end @error('itens.'.$item->id) is-invalid @enderror"
                                                   value="{{ old('itens.'.$item->id, 0) }}" aria-describedby="max-{{ $item->id }}">
                                            <small class="text-muted" id="max-{{ $item->id }}">até {{ $disponivel }}</small>
                                        @else
                                            <span class="text-muted">já voltou tudo</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @if ($venda->desconto > 0)
                        <p class="form-text mt-3 mb-0">Esta venda teve desconto de R$ {{ number_format($venda->desconto, 2, ',', '.') }}: o valor devolvido de cada produto já sai com o desconto repartido.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bloco 2: por que voltou e para onde vai --}}
        <div class="col-xl-5">
            <div class="card cartao-form h-100">
                <div class="card-body">
                    <h2 class="grupo-form">Motivo e destino</h2>

                    <div class="mb-3">
                        <label for="motivo" class="form-label">Motivo <span class="text-danger" aria-hidden="true">*</span></label>
                        <select name="motivo" id="motivo" aria-required="true" class="form-select @error('motivo') is-invalid @enderror">
                            <option value="">Selecione...</option>
                            @foreach (\App\Models\Devolucao::MOTIVOS as $chave => $motivo)
                                <option value="{{ $chave }}" data-destino="{{ $motivo['destino'] }}" @selected(old('motivo') === $chave)>{{ $motivo['nome'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label">O que acontece com o produto <span class="text-danger" aria-hidden="true">*</span></legend>
                        @foreach (\App\Models\Devolucao::DESTINOS as $chave => $nome)
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="destino" id="destino_{{ $chave }}" value="{{ $chave }}" @checked(old('destino', 'estoque') === $chave)>
                                <label class="form-check-label" for="destino_{{ $chave }}">
                                    {{ $nome }}
                                    <span class="d-block text-muted">
                                        @switch($chave)
                                            @case('estoque') O produto está bom e volta para a prateleira (soma no estoque). @break
                                            @case('perda') Não dá para vender de novo; a loja assume o prejuízo. @break
                                            @case('fornecedor') Fica separado para trocar ou devolver ao fornecedor. @break
                                        @endswitch
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </fieldset>

                    <div class="mb-3">
                        <label for="observacao" class="form-label">Observação</label>
                        <textarea name="observacao" id="observacao" rows="2" maxlength="500" class="form-control" placeholder="Ex.: embalagem aberta, troca por outro tamanho">{{ old('observacao') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="pe-form">
                <a href="{{ route('vendas.show', $venda) }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Registrar devolução</button>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    // Cada motivo sugere um destino (defeito → fornecedor, avariado → perda...). A pessoa pode trocar.
    document.getElementById('motivo').addEventListener('change', (e) => {
        const destino = e.target.selectedOptions[0]?.dataset.destino;
        const opcao = destino && document.getElementById('destino_' + destino);
        if (opcao) opcao.checked = true;
    });
</script>
@endpush
