<?php

namespace App\Support;

// Funções de apoio para textos exibidos nas telas
class Texto
{
    // Iniciais para os "avatares": "Arroz 5kg" -> "AR", "Maria da Silva" -> "MS"
    // (ignora números e palavras curtas como "de", "da")
    public static function iniciais(string $nome): string
    {
        $palavras = array_values(array_filter(
            preg_split('/\s+/', trim($nome)),
            fn ($p) => preg_match('/^\p{L}{3,}/u', $p)
        ));

        if (! $palavras) {
            return mb_strtoupper(mb_substr($nome, 0, 2));
        }

        return mb_strtoupper(count($palavras) > 1
            ? mb_substr($palavras[0], 0, 1).mb_substr(end($palavras), 0, 1)
            : mb_substr($palavras[0], 0, 2));
    }
}
