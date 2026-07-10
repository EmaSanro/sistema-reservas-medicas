<?php
namespace App\Shared\Search;

use App\Shared\Exceptions\ValidationException;

/**
 * Base class for per-module search filter validators.
 *
 * Subclasses declare the allowed columns and their per-value rules via
 * {@see self::definitions()}. The template method {@see self::validar()}
 * enforces the whitelist, empty-string check and per-column validation.
 */
abstract class SearchFilterValidator {
    /**
     * @return array<string, FilterDefinition> keyed by query param name
     */
    abstract public static function definitions(): array;

    /**
     * @param array<string, mixed> $params query params (already stripped of reserved keys like page/limit)
     * @return array<string, string> validated key => trimmed value
     * @throws ValidationException when at least one param fails validation
     */
    public static function validar(array $params): array {
        $definitions = static::definitions();
        $filtros = [];
        $errors = [];

        foreach($params as $key => $rawValue) {
            if(!isset($definitions[$key])) {
                $errors[$key] = "Filtro no permitido. Permitidos: " . implode(", ", array_keys($definitions));
                continue;
            }

            if(!is_string($rawValue)) {
                $errors[$key] = "El valor para {$key} debe ser una cadena";
                continue;
            }

            $value = trim($rawValue);
            if($value === '') {
                $errors[$key] = "El valor para {$key} no puede estar vacío";
                continue;
            }

            $def = $definitions[$key];
            if($def->validator !== null) {
                $err = ($def->validator)($value);
                if($err !== null) {
                    $errors[$key] = $err;
                    continue;
                }
            }

            $filtros[$key] = $value;
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $filtros;
    }
}
