<?php
namespace App\Repository;

use AppConfig\Database;

class AuthRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function buscarUsuario($data, $password) {
        $query = $this->db->prepare("
            SELECT * FROM usuario WHERE email = ? OR telefono = ?
        ");
        $query->execute([$data, $data]);
        $usuario = $query->fetch();
        if($usuario && password_verify($password, $usuario["password"])) {
            if($usuario["rol"] == "Profesional") {
                $query = $this->db->prepare("
                    SELECT * FROM profesional WHERE idprofesional = ?
                ");
                $query->execute([$usuario["id"]]);
                $prof = $query->fetch();
                return [
                    "id" => $usuario["id"],
                    "nombre" => $usuario["nombre"],
                    "apellido" => $usuario["apellido"],
                    "profesion" => $prof["profesion"],
                    "email" => $usuario["email"] ?? "",
                    "telefono" => $usuario["telefono"] ?? ""
                ];
            }
            return [
                "id" => $usuario["id"],
                "nombre" => $usuario["nombre"],
                "apellido" => $usuario["apellido"],
                "email" => $usuario["email"] ?? "",
                "telefono" => $usuario["telefono"] ?? ""
            ];
        } else {
            return null;
        }
    }
}