<?php

// Arquivo criado com o comando:
//   php artisan make:model Cliente

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    // Nome da tabela no banco de dados
    protected $table = 'clientes';

    // Campos que podem ser preenchidos em massa
    protected $fillable = ['nome', 'cpf_cnpj', 'telefone', 'email'];
}
