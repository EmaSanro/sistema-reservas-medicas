<?php
namespace App\Auth\Repository;

use App\Auth\Model\Usuario;
use App\Shared\Repository;

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