<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Ordena a listagem pela coluna clicada no cabeçalho da tabela (?ordem=preco&dir=desc).
     *
     * $colunas = ['nome_na_url' => 'coluna_no_banco']. Só as colunas dessa lista são aceitas,
     * assim ninguém consegue ordenar por uma coluna qualquer digitando na URL.
     * Devolve [ordem, direção] para a view destacar a coluna ativa.
     */
    protected function ordenar(Builder $query, Request $request, array $colunas, string $padrao, string $dirPadrao = 'asc'): array
    {
        $ordem = array_key_exists($request->input('ordem'), $colunas) ? $request->input('ordem') : $padrao;
        $dir = in_array($request->input('dir'), ['asc', 'desc'], true) ? $request->input('dir') : $dirPadrao;

        $query->orderBy($colunas[$ordem], $dir)->orderBy($query->getModel()->getTable().'.id', $dir);

        return [$ordem, $dir];
    }
}
