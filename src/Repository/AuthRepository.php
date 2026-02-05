<?php
namespace App\Repository;

use App\Exceptions\DatabaseException;
use App\Model\Profesional;
use App\Model\Roles;
use App\Model\Usuario;
use AppConfig\Database;
use PDO;

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
        $usuario = $query->fetch(PDO::FETCH_ASSOC);

        if(!$usuario) return null;
        
        if($usuario["rol"] == Roles::PROFESIONAL) {
            $query = $this->db->prepare("
            SELECT * FROM profesional WHERE idprofesional = ?
            ");
            $query->execute([$usuario["id"]]);
            $prof = $query->fetch();
            return new Profesional(
                $usuario["id"],
                $usuario["nombre"],
                $usuario["apellido"],
                $prof["profesion"],
                $usuario["email"] ?? "",
                $usuario["telefono"] ?? "",
                $usuario["password"]
            );
        }
        return new Usuario(
            $usuario["id"],
            $usuario["nombre"],
            $usuario["apellido"],
            $usuario["rol"],
            $usuario["email"] ?? "",
            $usuario["telefono"] ?? "",
            $usuario["password"]
        );
    }
}