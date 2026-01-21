<?php
namespace App\Controller;
class Validaciones {

    public static function validarID($id) {
        if(!isset($id)) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "Asegurate de ingresar un ID"
            ]);
            return;
        }
        if(!is_numeric($id)) {
            http_response_code(400);
            echo json_encode([
                "ERROR" => "Asegurate de que el ID sea un numero valido"
            ]);
            return;
        }
    }

    public static function validarInput($input) {
        if(!$input) {
            http_response_code(400);
            throw new \JsonException("JSON Invalido");
        }
    }

    public static function validarCriteriosPassword($password) {
        if(strlen($password) < 8) {
            http_response_code(400);
            echo json_encode([
                "Invalido" => "La contraseña debe contener minimo 8 caracteres"
            ]);
            return;
        }
        if(!preg_match("/[A-Z]/", $password)) {
            http_response_code(400);
            echo json_encode([
                "Invalido" => "La contraseña tiene que tener una letra en mayuscula"
            ]);
            return;
        }
        if(!preg_match("/[^a-zA-Z0-9]/", $password)) {
            http_response_code(400);
            echo json_encode([
                "Invalido" => "La contraseña debe tener un caracter especial"
            ]);
            return;
        }
    }

    public static function validarLogin($input) {
        if((!isset($input["telefono"]) && !isset($input["email"]) || !isset($input["password"]))) {
            http_response_code(400);
            throw new \InvalidArgumentException("Debes ingresar tu email/telefono y contraseña para loguearte");
        }
    } 
}