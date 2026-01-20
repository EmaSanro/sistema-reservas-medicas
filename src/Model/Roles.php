<?php
namespace App\Model;
class Roles {
    public const PACIENTE = "Paciente";
    public const PROFESIONAL = "Profesional";
    public const ADMIN = "Admin";

    public static function todos(): array {
        return [
            self::ADMIN,
            self::PROFESIONAL,
            self::PACIENTE
        ];
    }
}