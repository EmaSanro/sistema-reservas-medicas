<?php
namespace App\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private static function getSecretKey(): string {
        $key = $_ENV['SECRET_KEY'] ?? null;
        if (!$key) {
            throw new \Exception("La clave secreta no está configurada en las variables de entorno.");
        }
        return $key;
    }
    public static function generateToken($data) {
        $time = time();

        $token = [
            "iat" => $time,
            "exp" => $time * (60*60),
            "data" => $data 
        ];

        return JWT::encode($token, self::getSecretKey(), "HS256");
    }

    public static function validateToken() {
        $headers = apache_request_headers();

        if(!isset($headers["Authorization"])) {
            throw new \Exception("Token no proporcionado");
        }

        $authHeader = $headers["Authorization"];
        $token = str_replace("Bearer ", "", $authHeader);

        try {
            $decoded = JWT::decode($token, new Key(self::getSecretKey(), "HS256"));
            return $decoded->data;
        } catch (\Exception $e) {
            throw new \Exception("Token invalido o expirado");
        }
    }
}