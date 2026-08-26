<?php
namespace App\Pacientes\Validators;

use App\Shared\Search\FilterDefinition;
use App\Shared\Search\FilterOperator;
use App\Shared\Search\SearchFilterValidator;

final class PacienteSearchValidator extends SearchFilterValidator {

    public static function definitions(): array {
        $soloLetras = static fn(string $v): ?string =>
            preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/", $v)
                ? null : "Solo puede contener letras y espacios";

        $soloDigitos = static fn(string $v): ?string =>
            preg_match("/^\d+$/", $v)
                ? null : "Debe contener solo dígitos";

        return [
            'nombre'   => new FilterDefinition('nombre',   'nombre',   FilterOperator::LIKE_CONTAINS, $soloLetras),
            'apellido' => new FilterDefinition('apellido', 'apellido', FilterOperator::LIKE_CONTAINS, $soloLetras),
            'email'    => new FilterDefinition('email',    'email',    FilterOperator::LIKE_CONTAINS),
            'telefono' => new FilterDefinition('telefono', 'telefono', FilterOperator::LIKE_CONTAINS, $soloDigitos),
        ];
    }
}
