<?php
namespace App\Auth\Validators;

use App\Shared\Exceptions\ValidationException;

class AuthValidator {

    public static function validateInputLogin(array $input) {
        $errors = [];
        if(!(isset($input['telefono']) || !isset($input['email']))) {
            $errors["telefono/email"] = ['Debes ingresar tu telefono o email para loguearte'];
        }
        if(!isset($input['password'])) {
            $errors["password"] = ['Debes ingresar tu contraseña para loguearte'];
        }

        if(isset($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors["email"] = ['El email ingresado no es valido'];
        }

        if(isset($input["telefono"])) {
            if(!preg_match("/{0-9}/", $input["telefono"])) {
                $errors["telefono"] = ['El telefono solo puede contener numeros'];
            }
        }

        if(!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}