<?php

// Mensagens de validação em português.
// ":attribute" é trocado pelo nome amigável do campo (lista "attributes" no final).

return [
    'accepted' => 'O campo :attribute deve ser aceito.',
    'array' => 'O campo :attribute deve ser uma lista.',
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação de :attribute não confere.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'exists' => 'O :attribute selecionado é inválido.',
    'in' => 'O :attribute selecionado é inválido.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'max' => [
        'array' => 'O campo :attribute não pode ter mais de :max itens.',
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'string' => 'O campo :attribute não pode ter mais de :max caracteres.',
    ],
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min item(ns).',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'numeric' => 'O campo :attribute deve ser um número.',
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Já existe um cadastro com este :attribute.',

    // Nomes amigáveis dos campos
    'attributes' => [
        'nome' => 'nome',
        'descricao' => 'descrição',
        'cnpj' => 'CNPJ',
        'cpf_cnpj' => 'CPF/CNPJ',
        'telefone' => 'telefone',
        'email' => 'e-mail',
        'password' => 'senha',
        'preco' => 'preço',
        'estoque' => 'estoque',
        'estoque_minimo' => 'estoque mínimo',
        'categoria_id' => 'categoria',
        'fornecedor_id' => 'fornecedor',
        'cliente_id' => 'cliente',
        'produto_id' => 'produto',
        'data' => 'data',
        'observacao' => 'observação',
        'itens' => 'itens',
        'itens.*.produto_id' => 'produto',
        'itens.*.quantidade' => 'quantidade',
        'tipo' => 'tipo',
        'quantidade' => 'quantidade',
        'motivo' => 'motivo',
    ],
];
