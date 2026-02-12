<?php
namespace App\Security;

use App\Exceptions\ArchivoNota\SubidaArchivoException;
use App\Exceptions\ArchivoNota\TamanioArchivoException;
use App\Exceptions\ArchivoNota\TipoArchivoInvalidoException;
use App\Exceptions\Auth\InvalidJSONException;
use App\Model\TiposArchivosPermitidos;

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

    public static function validarArchivo(array $archivo) {
        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new SubidaArchivoException("Error al subir archivo");
        }
        
        // Validar tipo
        if (!in_array($archivo['type'], TiposArchivosPermitidos::obtenerTodos())) {
            throw new TipoArchivoInvalidoException("Solo se permiten archivos PDF, JPG, JPEG y PNG");
        }
        
        // Validar tamaño
        $tamanioMaximo = 5 * 1024 * 1024; // 5MB
        if ($archivo['size'] > $tamanioMaximo) {
            throw new TamanioArchivoException("El archivo no puede superar 5MB");
        }
        
        // Validación adicional de extensión (seguridad)
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        if(!in_array($extension, $extensionesPermitidas)) {
            throw new TipoArchivoInvalidoException("Extensión de archivo no permitida");
        }
    }
}