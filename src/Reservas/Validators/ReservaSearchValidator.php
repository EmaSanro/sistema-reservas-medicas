<?php
namespace App\Reservas\Validators;

use App\Reservas\Model\EstadoReserva;
use App\Shared\Search\FilterDefinition;
use App\Shared\Search\FilterOperator;
use App\Shared\Search\SearchFilterValidator;

final class ReservaSearchValidator extends SearchFilterValidator {

    public static function definitions(): array {
        $enteroPositivo = static fn(string $v): ?string =>
            (ctype_digit($v) && (int)$v > 0)
                ? null : "Debe ser un número entero positivo";

        $estadoValido = static fn(string $v): ?string =>
            in_array($v, EstadoReserva::todos(), true)
                ? null : "Valores permitidos: " . implode(", ", EstadoReserva::todos());

        $fechaValida = static fn(string $v): ?string =>
            preg_match("/^\d{4}(-\d{2}(-\d{2})?)?$/", $v)
                ? null : "Formato requerido: YYYY, YYYY-MM o YYYY-MM-DD";

        return [
            'idprofesional' => new FilterDefinition('idprofesional', 'idprofesional', FilterOperator::EQUALS, $enteroPositivo),
            'idpaciente'    => new FilterDefinition('idpaciente',    'idpaciente',    FilterOperator::EQUALS, $enteroPositivo),
            'estado'        => new FilterDefinition('estado',        'estado',        FilterOperator::EQUALS, $estadoValido),
            'fecha_reserva' => new FilterDefinition('fecha_reserva', 'fecha_reserva', FilterOperator::LIKE_STARTS_WITH, $fechaValida),
        ];
    }
}
