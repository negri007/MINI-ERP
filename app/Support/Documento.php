<?php

namespace App\Support;

// Funções de apoio para CPF e CNPJ (formatar e conferir os dígitos verificadores)
class Documento
{
    // Deixa só os números: "529.982.247-25" -> "52998224725"
    public static function digitos(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    // Formata com a máscara padrão; se não tiver 11 ou 14 dígitos, devolve como veio
    public static function formatar(?string $valor): ?string
    {
        $d = self::digitos($valor);

        return match (strlen($d)) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $d),
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $d),
            default => $valor,
        };
    }

    // Confere os dígitos verificadores do CPF
    public static function cpfValido(string $valor): bool
    {
        $cpf = self::digitos($valor);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cpf[$i] * (($t + 1) - $i);
            }
            $digito = ((10 * $soma) % 11) % 10;
            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }

    // Confere os dígitos verificadores do CNPJ
    public static function cnpjValido(string $valor): bool
    {
        $cnpj = self::digitos($valor);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        foreach ([12, 13] as $t) {
            $soma = 0;
            $peso = $t - 7;
            for ($i = 0; $i < $t; $i++) {
                $soma += $cnpj[$i] * $peso;
                $peso = $peso === 2 ? 9 : $peso - 1;
            }
            $digito = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
            if ((int) $cnpj[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }
}
