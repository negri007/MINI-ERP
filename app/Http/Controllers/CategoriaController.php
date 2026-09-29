<?php

// Arquivo criado com o comando:
//   php artisan make:controller CategoriaController --resource
// (--resource já cria os métodos index, create, store, show, edit, update e destroy)

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaRequest;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // Lista as categorias: busca, filtro (com/sem produtos), ordenação e paginação
    public function index(Request $request)
    {
        $busca = $request->input('busca');
        $filtro = $request->input('filtro');

        $query = Categoria::withCount('produtos')
            // Os 6 primeiros produtos de cada categoria aparecem ao abrir a linha
            ->with(['produtos' => fn ($q) => $q->orderBy('nome')->limit(6)])
            ->busca($busca)
            ->when($filtro === 'com', fn ($q) => $q->has('produtos'))
            ->when($filtro === 'vazias', fn ($q) => $q->doesntHave('produtos'));

        [$ordem, $dir] = $this->ordenar($query, $request, [
            'nome' => 'nome',
            'produtos' => 'produtos_count',
        ], 'nome');

        $categorias = $query->paginate(10)->withQueryString(); // mantém filtros ao trocar de página

        // Contagens das pílulas de filtro (respeitando a busca)
        $contagem = [
            'todas' => Categoria::busca($busca)->count(),
            'com' => Categoria::busca($busca)->has('produtos')->count(),
            'vazias' => Categoria::busca($busca)->doesntHave('produtos')->count(),
        ];

        return view('categorias.index', compact('categorias', 'contagem', 'filtro', 'ordem', 'dir'));
    }

    // Exibe o formulário de cadastro
    public function create()
    {
        return view('categorias.create', ['categoria' => new Categoria]);
    }

    // Salva uma nova categoria (a validação acontece no CategoriaRequest)
    public function store(CategoriaRequest $request)
    {
        Categoria::create($request->validated());

        return redirect()->route('categorias.index')->with('success', 'Categoria cadastrada com sucesso!');
    }

    // Exibe o formulário de edição
    public function edit(Categoria $categoria)
    {
        return view('categorias.edit', compact('categoria'));
    }

    // Atualiza uma categoria existente
    public function update(CategoriaRequest $request, Categoria $categoria)
    {
        $categoria->update($request->validated());

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    // Exclui uma categoria (bloqueia se houver produtos vinculados)
    public function destroy(Categoria $categoria)
    {
        if ($categoria->produtos()->exists()) {
            return redirect()->route('categorias.index')->with('error', 'Não é possível excluir: existem produtos nesta categoria.');
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
