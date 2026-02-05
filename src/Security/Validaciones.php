<?php
namespace App\Security;

use App\Exceptions\Auth\InvalidJSONException;

class Validaciones {

    public static function validarID($id) {
        if(!isset($id)) {
            http_response_code(400);
            throw new \BadFunctionCallException("Asegurate de ingresar un ID");
        }
        if(!is_numeric($id)) {
            http_response_code(400);
            throw new \InvalidArgumentException("Asegurate de que el ID sea un numero valido");
        }
    }

    public static function validarInput($input) {
        if(!$input) {
            http_response_code(400);
            throw new InvalidJSONException("JSON Invalido");
        }
    }

    public static function validarCriteriosPassword($password) {
        if(strlen($password) < 8) {
            http_response_code(400);
            throw new \LengthException("La contraseña debe contener minimo 8 caracteres");
        }
        if(!preg_match("/[A-Z]/", $password)) {
            http_response_code(400);
            throw new \InvalidArgumentException("La contraseña tiene que tener una letra en mayuscula");
        }
        if(!preg_match("/[^a-zA-Z0-9]/", $password)) {
            http_response_code(400);
            throw new \InvalidArgumentException("La contraseña debe tener un caracter especial");
        }
    }

    public static function validarLogin($input) {
        if((!isset($input["telefono"]) && !isset($input["email"]) || !isset($input["password"]))) {
            http_response_code(400);
            throw new \InvalidArgumentException("Debes ingresar tu email/telefono y contraseña para loguearte");
        }
    } 
}