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

    public function buscarCoincidencia(Usuario $usuario): Usuario|null {
        $sql = sprintf("SELECT * FROM %s", $this->getTableName());
        if($usuario->getEmail() && $usuario->getTelefono()) {
            $sql .= " WHERE (email = :email OR telefono = :telefono)";
            $usuarioExistente = $this->findOneByQuery($sql, ["email" => $usuario->getEmail(), "telefono" => $usuario->getTelefono()]);
        } elseif ($usuario->getEmail()) {
            $sql .= " WHERE email = :email";
            $usuarioExistente = $this->findOneByQuery($sql, ["email" => $usuario->getEmail()]);
        } elseif ($usuario->getTelefono()) {
            $sql .= " WHERE telefono = :telefono";
            $usuarioExistente = $this->findOneByQuery($sql, ["telefono" => $usuario->getTelefono()]);
        }

        return $usuarioExistente;
    }
}