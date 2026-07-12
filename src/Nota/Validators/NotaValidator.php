<?php
namespace App\Nota\Validators;

use App\Shared\Exceptions\ValidationException;

class NotaValidator {

    private const CAMPOS_REQUERIDOS = ["texto_nota", "reserva_id", "motivo_visita"];

    public static function validarID(string $id): void {
        $errors = [];
        if(!ctype_digit($id) || (int)$id <= 0) {
            $errors["id"] = ["El ID debe ser un número entero positivo."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestCrear(array $data): void {
        $errors = [];

        foreach(self::CAMPOS_REQUERIDOS as $campo) {
            if(empty($data[$campo]) || $data[$campo] === "") {
                $errors[$campo] = ["El campo $campo es obligatorio."];
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }

        if(!ctype_digit($data["reserva_id"]) || (int)$data["reserva_id"] <= 0) {
            $errors["reserva_id"] = ["El ID de reserva debe ser un número entero positivo."];
        }

        if(!is_string($data["motivo_visita"])) {
            $errors["motivo_visita"] = ["El motivo de visita es obligatorio."];
        } elseif(!preg_match("/^[a-zA-Z0-9\s]+$/", $data["motivo_visita"])) {
            $errors["motivo_visita"] = ["El motivo de visita solo puede contener letras, números y espacios."];
        }

        if(!is_string($data["texto_nota"])) {
            $errors["texto_nota"] = ["El cuerpo de la nota es obligatorio."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestActualizar(array $data): void {
        $errors = [];

        if(isset($data["motivo_visita"])) {
            if(!is_string($data["motivo_visita"])) {
                $errors["motivo_visita"] = ["El motivo de visita es obligatorio."];
            } elseif(!preg_match("/^[a-zA-Z0-9\s]+$/", $data["motivo_visita"])) {
                $errors["motivo_visita"] = ["El motivo de visita solo puede contener letras, números y espacios."];
            }
        }

        if(isset($data["texto_nota"]) && !is_string($data["texto_nota"])) {
            $errors["texto_nota"] = ["El cuerpo de la nota es obligatorio."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}