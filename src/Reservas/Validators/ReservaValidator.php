<?php
namespace App\Reservas\Validators;

use App\Shared\Exceptions\ValidationException;
use DateTime;

class ReservaValidator {

    private const CAMPOS_REQUERIDOS = ["idProfesional", "fecha_reserva"];

    public static function validarRequestCrear(array $data) {
        $errors = [];

        foreach(self::CAMPOS_REQUERIDOS as $campo) {
            if(!isset($data[$campo]) || empty($data[$campo])) {
                $errors[$campo] = ["El campo $campo es requerido."];
            }
        }

        if(!ctype_digit($data["idProfesional"]) || (int)$data["idProfesional"] <= 0) {
            $errors["idProfesional"] = ["El ID del profesional debe ser un número entero positivo."];
        }

        if(isset($data["fecha_reserva"])) {
            $fecha = DateTime::createFromFormat("Y-m-d H:i:s", $data["fecha_reserva"]);
            if(!$fecha) {
                $errors["fecha_reserva"] = ["La fecha debe tener el formato 'Y-m-d H:i:s'."];
            } elseif ($fecha < new DateTime()) {
                $errors["fecha_reserva"] = ["La fecha de reserva no puede ser en el pasado."];
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestActualizar(array $data) {
        $errors = [];

        if(isset($data["fecha_reserva"])) {
            $fecha = DateTime::createFromFormat("Y-m-d H:i:s", $data["fecha_reserva"]);
            if(!$fecha) {
                $errors["fecha_reserva"] = ["La fecha debe tener el formato 'Y-m-d H:i:s'."];
            } elseif ($fecha < new DateTime()) {
                $errors["fecha_reserva"] = ["La fecha de reserva no puede ser en el pasado."];
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarID(string $id) {
        $errors = [];
        if(!ctype_digit($id) || (int)$id <= 0) {
            $errors["id"] = ["El ID debe ser un número entero positivo."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}