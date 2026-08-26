<?php
namespace App\Shared\Search;

use Closure;

/**
 * Immutable value object describing a searchable column.
 *
 * - queryParam: name expected in the URL (?queryParam=value).
 * - sqlColumn: qualified column name used in the WHERE clause (e.g. "u.nombre" or "fecha_reserva").
 * - operator: how to compare the value against the column.
 * - validator: optional closure that receives the trimmed string value and
 *   returns null on success or an error message on failure.
 * - requiredJoin: optional SQL JOIN fragment that must be included when this
 *   filter is active. If two filters declare the same fragment it is only
 *   added once by the query builder.
 */
final class FilterDefinition {
    public function __construct(
        public readonly string $queryParam,
        public readonly string $sqlColumn,
        public readonly FilterOperator $operator = FilterOperator::LIKE_CONTAINS,
        public readonly ?Closure $validator = null,
        public readonly ?string $requiredJoin = null,
    ) {}
}
