<?php
namespace App\Shared\Search;

final class LikeEscaper {
    public static function escape(string $value): string {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
