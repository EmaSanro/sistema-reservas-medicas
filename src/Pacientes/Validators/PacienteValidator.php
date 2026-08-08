<?php
namespace App\Pacientes\Validators;

use App\Shared\Exceptions\ValidationException;
use App\Shared\Validators\NumberValidator;

class PacienteValidator {

    private const CAMPOS_REQUERIDOS = ["nombre", "apellido", "password"];
    public static function validarID(string $id) {
        $errors = [];
        if(!NumberValidator::esEnteroPositivo($id)) {
            $errors["id"] = ["El ID debe ser un número entero positivo."];
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestCrear(array $input) {
        $errors = [];
        foreach(self::CAMPOS_REQUERIDOS as $campo) {
            if(!isset($input[$campo]) || empty(trim($input[$campo]))) {
                $errors[$campo] = "El campo $campo es obligatorio!";
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }

        if(!isset($input["email"]) && !isset($input["telefono"])) {
            $errors["contacto"] = "Al menos un dato de contacto (email o teléfono) es obligatorio!";
        }

        if(!is_string($input["nombre"])) {
            $errors["nombre"] = "El nombre debe ser una cadena de texto";
        } elseif(!preg_match("/^[a-zA-Z\sñáéíóúÁÉÍÓÚ]+$/", $input["nombre"])) {
            $errors["nombre"] = "El nombre solo puede contener letras";
        }

        if(!is_string($input["apellido"])) {
            $errors["apellido"] = "El apellido debe ser una cadena de texto";
        } elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/", $input["apellido"])) {
            $errors["apellido"] = "El apellido solo puede contener letras y espacios";
        }

        if(isset($input["email"]) && !filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = "El email no tiene un formato válido";
        }

        if(isset($input["telefono"]) && !preg_match("/^\d{7,15}$/", $input["telefono"])) {
            $errors["telefono"] = "El teléfono debe contener solo dígitos y tener entre 7 y 15 caracteres";
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarRequestActualizar(array $input) {
        $errors = [];

        if(isset($input["nombre"])) {
            if(!is_string($input["nombre"])) {
                $errors["nombre"] = "El nombre debe ser una cadena de texto";
            } elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜ]+$/", $input["nombre"])) {
                $errors["nombre"] = "El nombre solo puede contener letras";
            }
        }

        if(isset($input["apellido"])) {
            if(!is_string($input["apellido"])) {
                $errors["apellido"] = "El apellido debe ser una cadena de texto";
            } elseif(!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/", $input["apellido"])) {
                $errors["apellido"] = "El apellido solo puede contener letras y espacios";
            }
        }

        if(isset($input["email"])) {
            if(!filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
                $errors["email"] = "El email no tiene un formato válido";
            }
        }

        if(isset($input["telefono"])) {
            if(!preg_match("/^\d{7,15}$/", $input["telefono"])) {
                $errors["telefono"] = "El teléfono debe contener solo dígitos y tener entre 7 y 15 caracteres";
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    public static function validarInputBajaPaciente(array $input) {
        $errors = [];
        if(!isset($input["motivo"]) || empty(trim($input["motivo"]))) {
            $errors["motivo"] = "El motivo de baja es obligatorio!";
        } elseif(mb_strlen($input["motivo"]) > 255) {
            $errors["motivo"] = "El motivo no puede contener mas de 255 caracteres!";
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}