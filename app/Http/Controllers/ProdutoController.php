<?php

// Arquivo criado com o comando:
//   php artisan make:controller ProdutoController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\ProdutoRequest;
use App\Models\Categoria;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Services\EstoqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProdutoController extends Controller
{
    // Lista os produtos: busca, filtro por categoria e estoque baixo, ordenação,
    // visualização em tabela ou vitrine, e os detalhes que aparecem ao abrir a linha
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $categoriaId = $request->input('categoria_id');
        $estoqueBaixo = $request->boolean('estoque_baixo');
        $visao = $request->input('visao') === 'vitrine' ? 'vitrine' : 'tabela';

        $query = Produto::with([
            'categoria',
            'fornecedor',
            'movimentacoes' => fn ($q) => $q->latest()->latest('id')->limit(3), // últimas 3 movimentações
        ])
            ->withCount('itensVenda') // produto já vendido não pode ser excluído
            ->busca($busca)
            ->when($categoriaId, fn ($q) => $q->where('categoria_id', $categoriaId))
            ->when($estoqueBaixo, fn ($q) => $q->estoqueBaixo());

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'nome' => 'nome',
            'preco' => 'preco',
            'estoque' => 'estoque',
            // ordena pelo nome da categoria (subconsulta)
            'categoria' => Categoria::select('nome')->whereColumn('categorias.id', 'produtos.categoria_id'),
        ], 'nome');

        $produtos = $query->paginate($visao === 'vitrine' ? 12 : 10)->withQueryString();

        // Pílulas: quantos produtos em cada categoria e com estoque baixo (respeitando a busca)
        $porCategoria = Produto::busca($busca)->selectRaw('categoria_id, COUNT(*) as total')->groupBy('categoria_id')->pluck('total', 'categoria_id');
        $categorias = Categoria::orderBy('nome')->get();
        $contagem = [
            'todos' => $porCategoria->sum(),
            'estoque_baixo' => Produto::busca($busca)->estoqueBaixo()->count(),
        ];

        $vendas7dias = $this->vendasUltimos7Dias($produtos->pluck('id')->all());

        return view('produtos.index', compact('produtos', 'categorias', 'porCategoria', 'contagem', 'visao', 'ordem', 'dir', 'vendas7dias'));
    }

    // Quantidade vendida por dia nos últimos 7 dias, para o mini gráfico de cada produto:
    // [produto_id => ['dias' => [q, q, ...7], 'quantidade' => total, 'valor' => total em R$]]
    private function vendasUltimos7Dias(array $ids): array
    {
        $inicio = Carbon::today()->subDays(6);

        $linhas = VendaItem::join('vendas', 'vendas.id', '=', 'venda_itens.venda_id')
            ->where('vendas.status', Venda::CONCLUIDA)
            ->whereDate('vendas.data', '>=', $inicio->toDateString())
            ->whereIn('venda_itens.produto_id', $ids)
            ->selectRaw('venda_itens.produto_id, DATE(vendas.data) as dia, SUM(venda_itens.quantidade) as quantidade, SUM(venda_itens.subtotal) as valor')
            ->groupBy('venda_itens.produto_id', 'dia')
            ->get();

        $resultado = [];
        foreach ($ids as $id) {
            $doProduto = $linhas->where('produto_id', $id)->keyBy('dia');
            $dias = [];
            for ($d = $inicio->copy(); $d->lte(Carbon::today()); $d->addDay()) {
                $dias[] = (int) ($doProduto[$d->toDateString()]->quantidade ?? 0);
            }
            $resultado[$id] = [
                'dias' => $dias,
                'quantidade' => array_sum($dias),
                'valor' => (float) $doProduto->sum('valor'),
            ];
        }

        return $resultado;
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('produtos.create', [
            'produto' => new Produto(['estoque_minimo' => 5]),
            'categorias' => Categoria::orderBy('nome')->get(),
            'fornecedores' => Fornecedor::orderBy('nome')->get(),
        ]);
    }

    // Salva um novo produto e registra o estoque inicial no histórico
    public function store(ProdutoRequest $request, EstoqueService $estoque)
    {
        DB::transaction(function () use ($request, $estoque) {
            $dados = $request->validated();
            $estoqueInicial = (int) $dados['estoque'];

            // O produto nasce com estoque 0 e o saldo entra como movimentação
            $produto = Produto::create(array_merge($dados, ['estoque' => 0]));

            if ($estoqueInicial > 0) {
                $estoque->entrada($produto, $estoqueInicial, 'Estoque inicial');
            }
        });

        return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Produto $produto)
    {
        return view('produtos.edit', [
            'produto' => $produto,
            'categorias' => Categoria::orderBy('nome')->get(),
            'fornecedores' => Fornecedor::orderBy('nome')->get(),
        ]);
    }

    // Atualiza um produto (o estoque não é alterado aqui)
    public function update(ProdutoRequest $request, Produto $produto)
    {
        $produto->update($request->validated());

        return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
    }

    // Exclui um produto (bloqueia se ele já apareceu em alguma venda)
    public function destroy(Produto $produto)
    {
        if ($motivo = $produto->motivoParaNaoExcluir()) {
            return redirect()->route('produtos.index')->with('error', $motivo);
        }

        $produto->delete();

        return redirect()->route('produtos.index')->with('success', 'Produto excluído com sucesso!');
    }
}
