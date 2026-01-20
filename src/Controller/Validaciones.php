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
            http_response_code(422);
            echo json_encode([
                "ERROR" => "JSON invalido"
            ]);
            return;
        }
    }
}