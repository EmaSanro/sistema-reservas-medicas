<?php
namespace App\Repository;

use App\Shared\Repository;
use App\Model\Usuario;

class AuthRepository extends Repository {

    protected function getTableName(): string {
        return "usuario";
    }

    protected function getEntityClass(): string {
        return Usuario::class;
    }

    public function buscarUsuario(string $valor): Usuario|null {
        $sql = sprintf("SELECT * FROM %s WHERE email = :email OR telefono = :telefono", $this->getTableName());
        $usuario = $this->findOneByQuery($sql, ["email" => $valor, "telefono" => $valor]);

        return $usuario;
    }
}