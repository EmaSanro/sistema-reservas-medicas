<?php
namespace App\Model;
class EstadoReserva {
    public const CONFIRMADA = "Confirmada";
    public const COMPLETADA = "Completada";
    public const NO_ASISTIO = "No asistio";
    public const CANCELADA = "Cancelada";

    public static function todos() {
        return [
            self::CONFIRMADA,
            self::COMPLETADA,
            self::NO_ASISTIO,
            self::CANCELADA
        ];
    }
}