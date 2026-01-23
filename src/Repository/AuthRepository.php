<?php
namespace App\Repository;

use App\Model\Roles;
use AppConfig\Database;

class AuthRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function buscarUsuario($data) {
        $query = $this->db->prepare("
            SELECT * FROM usuario WHERE email = ? OR telefono = ?
        ");
        $query->execute([$data, $data]);
        $usuario = $query->fetch();
        if($usuario["rol"] == Roles::PROFESIONAL) {
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
                "rol" => $prof["rol"],
                "email" => $usuario["email"] ?? "",
                "telefono" => $usuario["telefono"] ?? "",
                "password" => $usuario["password"]
            ];
        }
        return [
            "id" => $usuario["id"],
            "nombre" => $usuario["nombre"],
            "apellido" => $usuario["apellido"],
            "rol" => $usuario["rol"],
            "email" => $usuario["email"] ?? "",
            "telefono" => $usuario["telefono"] ?? "",
            "password" => $usuario["password"]
        ];
    }
}