<?php
namespace App\Consultorio\Validators;

use App\Shared\Exceptions\ValidationException;
use DateTime;

class ConsultorioValidator {
    
    private const CAMPOS_REQUERIDOS = ["direccion", "ciudad", "horario_apertura", "horario_cierre"];

    public static function validarID(string $id) {
        $errors = [];
        if(!ctype_digit($id) || (int)$id <= 0) {
            $errors["id"] = ["El ID debe ser un número entero positivo."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestCrear(array $input) {
        $errors = [];

        foreach(self::CAMPOS_REQUERIDOS as $campo) {
            if(empty($input[$campo]) || !isset($input[$campo])) {
                $errors[$campo] = ["El campo $campo es requerido"];
            }
        }

        if(!is_string($input["ciudad"])) {
            $errors["ciudad"] = ["El campo ciudad debe ser una cadena de texto."];
        } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚ\s]+$/", $input["ciudad"])) {
            $errors["ciudad"] = ["El campo ciudad solo puede contener letras y espacios."];
        }

        if(!is_string($input["direccion"])) {
            $errors["direccion"] = ["El campo direccion debe ser una cadena de texto."];
        } elseif (!preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚ\s]+$/", $input["direccion"])) {
            $errors["direccion"] = ["El campo direccion solo puede contener letras, números y espacios."];
        }

        if(!self::validarHorario(($input["horario_apertura"]))) {
            $errors["horario_apertura"] = ["El campo horario_apertura debe tener formato HH:MM (24hs)."];
        }

        if(!self::validarHorario(($input["horario_cierre"]))) {
            $errors["horario_apertura"] = ["El campo horario_cierre debe tener formato HH:MM (24hs)."];
        } elseif($input["horario_apertura"] >= $input["horario_cierre"]) {
            $errors["horario_cierre"] = ["El campo horario_cierre debe ser mayor al horario_apertura."];
        }

        if(isset($input["idProfesional"])) {
            if(!ctype_digit(strval($input["idProfesional"])) || (int)$input["idProfesional"] <= 0) {
                $errors["idProfesional"] = ["El campo idProfesional debe ser un número entero positivo."];
            }
        }
        
        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestActualizar(array $input) {
        $errors = [];

        if(isset($input["ciudad"])) {
            if(!is_string($input["ciudad"])) {
                $errors["ciudad"] = ["El campo ciudad debe ser una cadena de texto."];
            } elseif (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚ\s]+$/", $input["ciudad"])) {
                $errors["ciudad"] = ["El campo ciudad solo puede contener letras y espacios."];
            }
        }

        if(isset($input["direccion"])) {
            if(!is_string($input["direccion"])) {
                $errors["direccion"] = ["El campo direccion debe ser una cadena de texto."];
            } elseif (!preg_match("/^[a-zA-Z0-9áéíóúÁÉÍÓÚ\s]+$/", $input["direccion"])) {
                $errors["direccion"] = ["El campo direccion solo puede contener letras, números y espacios."];
            }
        }

        if(isset($input["horario_apertura"]) && !self::validarHorario(($input["horario_apertura"]))) {
            $errors["horario_apertura"] = ["El campo horario_apertura debe tener formato HH:MM (24hs)."];
        }

        if(isset($input["horario_cierre"]) && !self::validarHorario(($input["horario_cierre"]))) {
            $errors["horario_cierre"] = ["El campo horario_cierre debe tener formato HH:MM (24hs)."];
        } elseif(isset($input["horario_apertura"], $input["horario_cierre"]) && $input["horario_apertura"] >= $input["horario_cierre"]) {
            $errors["horario_cierre"] = ["El campo horario_cierre debe ser mayor al horario_apertura."];
        }

        if(isset($input["idProfesional"])) {
            if(!ctype_digit(strval($input["idProfesional"])) || (int)$input["idProfesional"] <= 0) {
                $errors["idProfesional"] = ["El campo idProfesional debe ser un número entero positivo."];
            }
        }
        
        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private static function validarHorario(string $hora) {
        $d = DateTime::createFromFormat('H:i', $hora);
        return $d && $d->format('H:i') === $hora;
    }
}