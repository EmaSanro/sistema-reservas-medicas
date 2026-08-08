<?php
namespace App\Shared\Validators;

class NumberValidator {
    public static function esEnteroPositivo(mixed $valor): bool {
        if (is_int($valor))    return $valor > 0;
        if (is_string($valor)) return ctype_digit($valor) && (int) $valor > 0;
        return false;
    }
}