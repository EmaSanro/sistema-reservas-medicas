<?php
namespace App\Shared\Search;

final class SearchQueryBuilder {
    /**
     * Turns validated filter values into WHERE clauses + bound params + required JOINs.
     *
     * @param array<string, FilterDefinition> $definitions keyed by query param name
     * @param array<string, string>           $filtros     validated key => value
     * @return array{where: list<string>, params: array<string, string>, joins: list<string>}
     */
    public static function build(array $definitions, array $filtros): array {
        $where = [];
        $params = [];
        $joins = [];

        foreach($filtros as $key => $value) {
            if(!isset($definitions[$key])) {
                continue;
            }
            $def = $definitions[$key];
            $paramName = 'filtro_' . preg_replace('/[^a-zA-Z0-9]/', '_', $key);

            switch($def->operator) {
                case FilterOperator::LIKE_CONTAINS:
                    $where[] = "{$def->sqlColumn} LIKE :{$paramName}";
                    $params[$paramName] = '%' . LikeEscaper::escape($value) . '%';
                    break;
                case FilterOperator::LIKE_STARTS_WITH:
                    $where[] = "{$def->sqlColumn} LIKE :{$paramName}";
                    $params[$paramName] = LikeEscaper::escape($value) . '%';
                    break;
                case FilterOperator::EQUALS:
                    $where[] = "{$def->sqlColumn} = :{$paramName}";
                    $params[$paramName] = $value;
                    break;
            }

            if($def->requiredJoin !== null && !in_array($def->requiredJoin, $joins, true)) {
                $joins[] = $def->requiredJoin;
            }
        }

        return [
            'where' => $where,
            'params' => $params,
            'joins' => $joins,
        ];
    }
}
