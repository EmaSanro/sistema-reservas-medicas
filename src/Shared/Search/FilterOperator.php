<?php
namespace App\Shared\Search;

enum FilterOperator: string {
    case LIKE_CONTAINS = 'LIKE_CONTAINS';
    case LIKE_STARTS_WITH = 'LIKE_STARTS_WITH';
    case EQUALS = 'EQUALS';
}
