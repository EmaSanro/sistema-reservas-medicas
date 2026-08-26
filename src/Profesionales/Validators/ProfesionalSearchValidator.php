<?php
namespace App\Profesionales\Validators;

use App\Shared\Search\FilterDefinition;
use App\Shared\Search\FilterOperator;
use App\Shared\Search\SearchFilterValidator;

final class ProfesionalSearchValidator extends SearchFilterValidator {

    private const CONSULTORIO_JOIN = "JOIN consultorio c ON p.idprofesional = c.idprofesional";

    public static function definitions(): array {
        $soloLetras = static fn(string $v): ?string =>
            preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]+$/", $v)
                ? null : "Solo puede contener letras y espacios";

        $soloDigitos = static fn(string $v): ?string =>
            preg_match("/^\d+$/", $v)
                ? null : "Debe contener solo dígitos";

        $direccion = static fn(string $v): ?string =>
            preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚüÜñÑ\s\.,#\-]+$/", $v)
                ? null : "Contiene caracteres no permitidos";

        return [
            'nombre'    => new FilterDefinition('nombre',    'u.nombre',    FilterOperator::LIKE_CONTAINS, $soloLetras),
            'apellido'  => new FilterDefinition('apellido',  'u.apellido',  FilterOperator::LIKE_CONTAINS, $soloLetras),
            'email'     => new FilterDefinition('email',     'u.email',     FilterOperator::LIKE_CONTAINS),
            'telefono'  => new FilterDefinition('telefono',  'u.telefono',  FilterOperator::LIKE_CONTAINS, $soloDigitos),
            'profesion' => new FilterDefinition('profesion', 'p.profesion', FilterOperator::LIKE_CONTAINS, $soloLetras),
            'ciudad'    => new FilterDefinition('ciudad',    'c.ciudad',    FilterOperator::LIKE_CONTAINS, $soloLetras, self::CONSULTORIO_JOIN),
            'direccion' => new FilterDefinition('direccion', 'c.direccion', FilterOperator::LIKE_CONTAINS, $direccion,  self::CONSULTORIO_JOIN),
        ];
    }
}
