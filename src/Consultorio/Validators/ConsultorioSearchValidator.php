<?php
namespace App\Consultorio\Validators;

use App\Shared\Search\FilterDefinition;
use App\Shared\Search\FilterOperator;
use App\Shared\Search\SearchFilterValidator;

final class ConsultorioSearchValidator extends SearchFilterValidator {

    public static function definitions(): array {
        $soloLetras = static fn(string $v): ?string =>
            preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/", $v)
                ? null : "Solo puede contener letras y espacios";

        $direccion = static fn(string $v): ?string =>
            preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s\.,#\-]+$/", $v)
                ? null : "Contiene caracteres no permitidos";

        $enteroPositivo = static fn(string $v): ?string =>
            (ctype_digit($v) && (int)$v > 0)
                ? null : "Debe ser un número entero positivo";

        return [
            'ciudad'        => new FilterDefinition('ciudad',        'ciudad',        FilterOperator::LIKE_CONTAINS, $soloLetras),
            'direccion'     => new FilterDefinition('direccion',     'direccion',     FilterOperator::LIKE_CONTAINS, $direccion),
            'idprofesional' => new FilterDefinition('idprofesional', 'idprofesional', FilterOperator::EQUALS,        $enteroPositivo),
        ];
    }
}
